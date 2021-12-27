<?php

namespace App\Controller;

use App\Entity\UserSkill;
use App\Entity\Training;
use App\Entity\User;
use App\Entity\UserOccupation;
use App\Entity\OccupationSkill;
use App\Entity\Position;
use App\Repository\UserRepository;
use App\Repository\SkillRepository;
use App\Repository\TrainingRepository;
use App\Repository\TrainingFeedbackRepository;
use App\Repository\OccupationRepository;
use App\Repository\OccupationSkillRepository;
use App\Repository\UserSearchRepository;
use App\Repository\PositionRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\UserOccupationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/get_occupation", name="get_occupation", methods={"GET"})
     */
    public function get_occupation(TrainingRepository $trainingRepository)
    {
        $training = $trainingRepository->findOneBy(['id' => 1]);

        $skills = $training->getToAcquireSkills();

        foreach ($skills as $skill) {
            dump($skill->getSkill());
        }
        die();
    }

    /**
     * @Route("/user_occupation", name="user_occupation", methods={"POST"})
     */
    public function user_occupation(Request $request, OccupationRepository $occupationRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $params = $request->request->all();
        if (
            !$user ||
            !is_array($params) ||
            !array_key_exists('currentOccupations', $params) ||
            !array_key_exists('desiredOccupations', $params) ||
            !array_key_exists('previousOccupations', $params)
        )
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        // Sauvegarde expérience utilisateur
        if (array_key_exists('userProfessionalExperience', $params))
            $user->setProfessionalExperience(intval($params['userProfessionalExperience']));

        $previousIds = (is_array($params['previousOccupations'])) ? array_filter($params['previousOccupations'], 'is_numeric'): null;
        $currentIds = (is_array($params['currentOccupations'])) ? array_filter($params['currentOccupations'], 'is_numeric') : null;
        $desiredIds = (is_array($params['desiredOccupations'])) ? array_filter($params['desiredOccupations'], 'is_numeric') : null;

        $previousOccupations = new ArrayCollection();
        $currentOccupations = new ArrayCollection();
        $desiredOccupations = new ArrayCollection();
        $userOccupations = $user->getUserOccupations();
        $newPreviousOccupations = new ArrayCollection($occupationRepository->findBy(['id' => $previousIds]));
        $newCurrentOccupations = new ArrayCollection($occupationRepository->findBy(['id' => $currentIds]));
        $newDesiredOccupations = new ArrayCollection($occupationRepository->findBy(['id' => $desiredIds]));

        foreach ($userOccupations as $userOccupation) {
            if ($userOccupation->getIsCurrent() === true) {
                $currentOccupations->add($userOccupation->getOccupation());
                if (!$newCurrentOccupations->contains($userOccupation->getOccupation()))
                    $user->removeUserOccupation($userOccupation);
            }
            if ($userOccupation->getIsPrevious() === true) {
                $previousOccupations->add($userOccupation->getOccupation());
                if (!$newPreviousOccupations->contains($userOccupation->getOccupation()))
                    $user->removeUserOccupation($userOccupation);
            }
            if ($userOccupation->getIsDesired() === true) {
                $desiredOccupations->add($userOccupation->getOccupation());
                if (!$newDesiredOccupations->contains($userOccupation->getOccupation()))
                    $user->removeUserOccupation($userOccupation);
            }
        }

        foreach ($newPreviousOccupations as $occupation) {
            if (!$previousOccupations->contains($occupation)) {
                $userOccupation = new UserOccupation();
                $userOccupation->setUser($user);
                $userOccupation->setOccupation($occupation);
                $userOccupation->setIsPrevious(true);
                $user->addUserOccupation($userOccupation);
                $em->persist($userOccupation);
            }
        }
        foreach ($newCurrentOccupations as $occupation) {
            if (!$currentOccupations->contains($occupation)) {
                $userOccupation = new UserOccupation();
                $userOccupation->setUser($user);
                $userOccupation->setOccupation($occupation);
                $userOccupation->setIsCurrent(true);
                $user->addUserOccupation($userOccupation);
                $em->persist($userOccupation);
            }
        }
        foreach ($newDesiredOccupations as $occupation) {
            if (!$desiredOccupations->contains($occupation)) {
                $userOccupation = new UserOccupation();
                $userOccupation->setUser($user);
                $userOccupation->setOccupation($occupation);
                $userOccupation->setIsDesired(true);
                $user->addUserOccupation($userOccupation);
                $em->persist($userOccupation);
            }
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/user_skill", name="user_skill", methods={"POST"})
     */
    public function user_skill(Request $request, SkillRepository $skillRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $params = $request->request->all();
        if (
            !$user ||
            !is_array($params) ||
            !array_key_exists('associatedSkills', $params) ||
            !array_key_exists('disassociatedSkills', $params)
        )
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        $associatedIds = (is_array($params['associatedSkills'])) ? array_filter($params['associatedSkills'], 'is_numeric'): null;
        $disassociatedIds = (is_array($params['disassociatedSkills'])) ? array_filter($params['disassociatedSkills'], 'is_numeric') : null;

        $allSkills = new ArrayCollection();
        $userSkills = $user->getUserSkills();
        $newAssociatedSkills = new ArrayCollection($skillRepository->findBy(['id' => $associatedIds]));
        $newDisassociatedSkills = new ArrayCollection($skillRepository->findBy(['id' => $disassociatedIds]));

        foreach ($userSkills as $userSkill) {
            $allSkills->add($userSkill->getSkill());

            if ($userSkill->getIsSelected() === true && !$newAssociatedSkills->contains($userSkill->getSkill())) {
                $userSkill->setIsSelected(false);
                $em->persist($userSkill);
            } else if ($userSkill->getIsSelected() === false && $newAssociatedSkills->contains($userSkill->getSkill())) {
                $userSkill->setIsSelected(true);
                $em->persist($userSkill);
            }
        }

        foreach ($newAssociatedSkills as $skill) {
            if (!$allSkills->contains($skill)) {
                $userSkill = new UserSkill();
                $userSkill->setUser($user);
                $userSkill->setSkill($skill);
                $userSkill->setIsSelected(true);
                $user->addUserSkill($userSkill);
                $em->persist($userSkill);
            }
        }
        foreach ($newDisassociatedSkills as $skill) {
            if (!$allSkills->contains($skill)) {
                $userSkill = new UserSkill();
                $userSkill->setUser($user);
                $userSkill->setSkill($skill);
                $userSkill->setIsSelected(false);
                $user->addUserSkill($userSkill);
                $em->persist($userSkill);
            }
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/user_training", name="user_training", methods={"POST"})
     */
    public function user_training(Request $request, TrainingRepository $trainingRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $trainings_ids = $request->request->get('trainings');
        if (!$user || !$trainings_ids || !is_array($trainings_ids))
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);
        
        $result = $trainingRepository->findBy(['id' => array_filter($trainings_ids, 'is_numeric')]);
        $trainings = ($result) ? new ArrayCollection($result) : null;
        $userTrainings = $user->getTrainings();

        if ($trainings) {
            foreach ($trainings as $training) {
                if (!$userTrainings || !$userTrainings->contains($training))
                $user->addTraining($training);
            }
        }
        
        if ($userTrainings) {
            foreach ($userTrainings as $userTraining) {
                if (($trainings && !$trainings->contains($userTraining)) || !$trainings) {
                    $user->removeTraining($userTraining);
                }
            }
        }
        
        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/skills", name="skills", methods={"GET"})
     */
    public function skills(SkillRepository $skillRepository, CacheInterface $cache)
    {
        $skills = $cache->get('skills', function(ItemInterface $item) use ($skillRepository) {
            $item->expiresAfter(15);
            return $skillRepository->findAll();
        });
        return $this->json($skills, 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/skills_by_occupation/{occupation_id}", name="skills_by_occupation", methods={"GET"})
     */
    public function skills_by_occupation($occupation_id, OccupationSkillRepository $occupationSkillRepository, SkillRepository $skillRepository, OccupationRepository $occupationRepository)
    {
        $occupation = [];
        $skills = [];

        $occupation = $occupationRepository->find($occupation_id);
        if ($occupation)
            $skills = new ArrayCollection($occupationSkillRepository->findBy(['occupation' => $occupation]));

        $results = [
            'occupation' => $occupation,
            'skills' => $skills
        ];

        return $this->json($results, 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/done_training/{id}", name="done_training", methods={"POST"})
     */
    public function done_training(Training $training)
    {
        $user = $this->getUser();
        $user->addTraining($training);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/undone_training/{id}", name="undone_training", methods={"POST"})
     */
    public function undone_training(Training $training)
    {
        $user = $this->getUser();
        $user->removeTraining($training);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/add_institution", name="add_institution", methods={"POST"})
     */
    public function add_institution(
        Request $request, 
        ValidatorInterface $validator, 
        UserPasswordEncoderInterface $passwordEncoder, 
        SluggerInterface $slugger, 
        UserRepository $userRepository
    )
    {
        $data = $request->request->all();
        
        if (!$data || !is_array($data) || !array_key_exists('name', $data) || empty($data['name']))
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        $alreadyExistsUser = $userRepository->findOneBy(['username' => $data['name']]);
        if ($alreadyExistsUser)
            return new JsonResponse(['message' => 'Instituion name already exists'], Response::HTTP_BAD_REQUEST);

        $user = new User();
        $user->setUsername($data['name']);
        if (array_key_exists('address', $data) && !empty($data['address']))
            $user->setAddress($data['address']);

        $email = $slugger->slug($user->getUsername()) . '.' . uniqid() . '@silkc-platform.org';
        $user->setEmail($email);
        $roles = [User::ROLE_INSTITUTION];
        $createdAt = new \DateTime('now');
        $password = random_bytes(10);
        $password = $passwordEncoder->encodePassword($user, $password);
        $apiToken = base64_encode(sha1($createdAt->format('Y-m-d H:i:s').$password, true));
        $user->setTokenCreatedAt($createdAt);
        $user->setCreatedAt($createdAt);
        $user->setApiToken($apiToken);
        $user->setRoles($roles);
        $user->setPassword($password);

        $errors = $validator->validate($user);
        if (count($errors) > 0) 
            return new Response((string) $errors, 400);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true, 'institution' => $user], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/toggle_user_searches_param", name="toggle_user_searches_param", methods={"POST"})
     */
    public function toggle_user_searches_param()
    {
        $user = $this->getUser();

        $user->setIsSearchesKept(!$user->getIsSearchesKept());

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(['result' => $user->getIsSearchesKept()], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/delete_search_history", name="delete_search_history", methods={"POST"})
     */
    public function delete_search_history(
        Request $request,
        UserRepository $userRepository,
        UserSearchRepository $userSearchRepository,
        OccupationRepository $occupationRepository,
        SkillRepository $skillRepository
    )
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $data = $request->request->all();

        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('id', $data))
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        if ($data['type'] === 'occupation') {
            $occupation = $occupationRepository->findOneBy(['id' => $data['id']]);
            if (!$occupation)
                return new JsonResponse(['message' => 'Bad parameter'], Response::HTTP_BAD_REQUEST);
            $searches = $userSearchRepository->findBy(['user' => $user, 'occupation' => $occupation, 'isActive' => true]);
            if ($searches) {
                foreach ($searches as $search) {
                    $search->setIsActive(false);
                    $em->persist($search);
                    $em->flush();
                }
            }
        } else if ($data['type'] === 'skill') {
            $skill = $skillRepository->findOneBy(['id' => $data['id']]);
            if (!$skill)
                return new JsonResponse(['message' => 'Bad parameter'], Response::HTTP_BAD_REQUEST);
            $searches = $userSearchRepository->findBy(['user' => $user, 'skill' => $skill, 'isActive' => true]);
            if ($searches) {
                foreach ($searches as $search) {
                    $search->setIsActive(false);
                    $em->persist($search);
                    $em->flush();
                }
            }
        } else
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/search_affected_users", name="search_affected_users", methods={"GET"})
     */
    public function search_affected_users(Request $request, UserRepository $userRepository)
    {
        $skills = $request->query->get('skills', null);

        $defaultData = ['count_all' => 0, 'count_listening' => 0];

        if (!$skills || !is_array($skills) || count($skills) == 0)
            return $this->json(['result' => true, 'data' => $defaultData], 200, ['Access-Control-Allow-Origin' => '*']);

        $result = $userRepository->searchAffectedUsers($skills);

        $data = (
            $result &&
            is_array($result) &&
            array_key_exists('count_all', $result) &&
            array_key_exists('count_listening', $result)
        ) ?
            $result :
            $defaultData;

        return $this->json(['result' => true, 'data' => $data], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/send_position_to_affected_users/{position_id}", name="send_position_to_affected_users", methods={"GET"})
     */
    public function send_position_to_affected_users($position_id, Request $request, UserRepository $userRepository, PositionRepository $positionRepository, TranslatorInterface $translator, MailerInterface $mailer)
    {
        $skills = $request->query->get('skills', null);

        if (!$skills || !is_array($skills) || count($skills) == 0 || !$position_id)
            return new JsonResponse(['message' => $translator->trans('no_skills_found_for_this_position')], Response::HTTP_BAD_REQUEST);

        $position = $positionRepository->find($position_id);
        if (!$position)
            return new JsonResponse(['message' => $translator->trans('no_position_found')], Response::HTTP_BAD_REQUEST);

        $now = new \DateTimeImmutable();

        $result = $userRepository->fetchAffectedUsers($skills);

        $recipients = ($result) ? new ArrayCollection($result) : null;

        $countUsers = 0;
        $countErrors = 0;

        $em = $this->getDoctrine()->getManager();

        if ($recipients && $recipients->count() > 0) {
            foreach ($recipients as $user) {
                $html = $this->render(
                    'emails/position.html.twig',
                    [
                        'position' => $position,
                    ])->getContent();

                $email = (new Email())
                    ->from('contact@silkc-platform.org')
                    ->to($user->getEmail())
                    ->subject($translator->trans('position_offer'))
                    ->text($translator->trans('position_offer'))
                    ->html($html);

                try {
                    $mailer->send($email);
                    $countUsers++;
                } catch (TransportExceptionInterface $exception) {
                    $countErrors++;
                }
            }
        }


        $position->setSentToAffectedUsersAt($now);
        $position->setIsSentToAffectedUsers(true);
        $em->persist($position);
        $em->flush();

        return $this->json(['result' => true, 'countUsers' => $countUsers, 'countErrors' => $countErrors], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/cron/fetch_lat_and_long", name="fetch_lat_and_long", methods={"GET"})
     */
    public function fetch_lat_and_long(HttpClientInterface $client, TrainingRepository $trainingRepository, UserRepository $userRepository)
    {
        $trainings = $trainingRepository->findWithoutLatitudeAndLongitude();
        $users = $userRepository->findWithoutLatitudeAndLongitude();

        $em = $this->getDoctrine()->getManager();

        if ($trainings) {
            foreach ($trainings as $training) {
                try {
                    $response = $client->request(
                        'GET',
                        'http://api.positionstack.com/v1/forward?access_key=f0dca21d14ea7051bd831f9e9a2808dd&query=' . $training->getLocation()
                    );

                    $data = $response->toArray();

                    if (!is_array($data) && array_key_exists('data', $data) && !empty($data['data']))
                        continue;

                    $row = current($data['data']);
                    if (!is_array($row) || !array_key_exists('latitude', $row) || !array_key_exists('longitude', $row))
                        continue;

                    $training->setLatitude($row['latitude']);
                    $training->setLongitude($row['longitude']);

                    $em->persist($training);
                    $em->flush();
                } catch (\Throwable $e) {

                }
            }
        }

        if ($users) {
            foreach ($users as $user) {
                try {
                    $response = $client->request(
                        'GET',
                        'http://api.positionstack.com/v1/forward?access_key=f0dca21d14ea7051bd831f9e9a2808dd&query=' . $user->getAddress()
                    );

                    $data = $response->toArray();

                    if (!is_array($data) && array_key_exists('data', $data) && !empty($data['data']))
                        continue;

                    $row = current($data['data']);
                    if (!is_array($row) || !array_key_exists('latitude', $row) || !array_key_exists('longitude', $row))
                        continue;

                    $user->setLatitude($row['latitude']);
                    $user->setLongitude($row['longitude']);

                    $em->persist($user);
                    $em->flush();
                } catch (\Throwable $e) {

                }
            }
        }

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/cron/convert_euro_zloty", name="convert_euro_zloty", methods={"GET"})
     */
    public function convert_euro_zloty(HttpClientInterface $client)
    {
        try {
            $response = $client->request(
                'GET',
                'http://api.nbp.pl/api/exchangerates/rates/a/eur?format=json'
            );

            $data = $response->toArray();

            if (!$data || !is_array($data) || !array_key_exists('rates', $data) || !is_array($data['rates']))
                throw new \Exception('Get currency rate error');

            $current = current($data['rates']);
            $rate = (is_array($current) && array_key_exists('mid', $current)) ?
                $current['mid'] :
                null;

            if (!$rate)
                throw new \Exception('Currency rate empty');

            dd($rate);
        } catch(\Throwable $e) {
            dd($e->getMessage());
        }

    }
}
