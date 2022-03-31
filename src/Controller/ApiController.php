<?php

namespace App\Controller;

use App\Entity\UserSkill;
use App\Entity\Training;
use App\Entity\User;
use App\Entity\UserTraining;
use App\Entity\UserOccupation;
use App\Entity\OccupationSkill;
use App\Entity\Position;
use App\Repository\UserRepository;
use App\Repository\UserTrainingRepository;
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
    public function user_training(Request $request, TrainingRepository $trainingRepository, UserTrainingRepository $userTrainingRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $params = $request->request->all();

        if (
            !$user ||
            !is_array($params) ||
            !array_key_exists('trainingsIsFollowed', $params) ||
            !array_key_exists('trainingsIsInterestingForMe', $params)
        )  return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        $trainingsIsFollowed = new ArrayCollection();
        $trainingsIsInterestingForMe = new ArrayCollection();
        $userTrainings = $user->getUserTrainings();
        $newTrainingsIsFollowed = new ArrayCollection($trainingRepository->findBy(['id' => array_filter($params['trainingsIsFollowed'], 'is_numeric')]));
        $newTrainingsIsInterestingForMe = new ArrayCollection($trainingRepository->findBy(['id' => array_filter($params['trainingsIsInterestingForMe'], 'is_numeric')]));
            
            
        foreach ($userTrainings as $userTraining) {
            if ($userTraining->getIsFollowed() === true) {
                $trainingsIsFollowed->add($userTraining->getTraining());
                if (!$newTrainingsIsFollowed->contains($userTraining->getTraining()))
                    $user->removeUserTraining($userTraining);
            }
            if ($userTraining->getIsInterestingForMe() === true) {
                $trainingsIsInterestingForMe->add($userTraining->getTraining());
                if (!$newTrainingsIsInterestingForMe->contains($userTraining->getTraining()))
                    $user->removeUserTraining($userTraining);
            }
        }
        
        foreach ($newTrainingsIsFollowed as $training) {
            if (!$trainingsIsFollowed->contains($training)) {
                $userTraining = new UserTraining();
                $userTraining->setUser($user);
                $userTraining->setTraining($training);
                $userTraining->setIsFollowed(true);
                $em->persist($userTraining);
            }
        }
        
        foreach ($newTrainingsIsInterestingForMe as $training) {
            if (!$trainingsIsInterestingForMe->contains($training)) {
                $userTraining = new UserTraining();
                $userTraining->setUser($user);
                $userTraining->setTraining($training);
                $userTraining->setIsInterestingForMe(true);
                $em->persist($userTraining);
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
    public function done_training(Training $training, UserTrainingRepository $userTrainingRepository)
    {
        $user = $this->getUser();
        $userTraining = $userTrainingRepository->findOneBy(['training' => $training, 'user' => $user]);

        if ($userTraining) {
            $userTraining->setIsFollowed(true);
        } else {
            $userTraining = new UserTraining();
            $userTraining->setUser($user);
            $userTraining->setTraining($training);
            $userTraining->setIsFollowed(true);
            $user->addUserTraining($userTraining);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($userTraining);
        $em->persist($user);
        $em->flush();
        
        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/undone_training/{id}", name="undone_training", methods={"POST"})
     */
    public function undone_training(Training $training, UserTrainingRepository $userTrainingRepository)
    {
        $user = $this->getUser();
        $userTraining = $userTrainingRepository->findOneBy(['training' => $training, 'user' => $user]);
        $em = $this->getDoctrine()->getManager();
        if ($userTraining->getIsInterestingForMe() == false) {
            $user->removeUserTraining($userTraining);
        } else {
            $userTraining->setIsFollowed(false);
            $em->persist($userTraining);
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }
  
    
    /**
     * @Route("/interested_training/{id}", name="interesting_training", methods={"POST"})
     */
    public function interested_training(Training $training, UserTrainingRepository $userTrainingRepository)
    {
        $user = $this->getUser();
        $userTraining = $userTrainingRepository->findOneBy(['training' => $training, 'user' => $user]);
        if ($userTraining) {
            $userTraining->setIsInterestingForMe(true);
            $userTraining->setIsUninterestingToMe(false);
        } else {
            $userTraining = new UserTraining();
            $userTraining->setUser($user);
            $userTraining->setTraining($training);
            $userTraining->setIsInterestingForMe(true);
            $userTraining->setIsUninterestingToMe(false);
            $user->addUserTraining($userTraining);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($userTraining);
        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/notinterested_training/{id}", name="notinterested_training", methods={"POST"})
     */
    public function notinterested_training(Training $training, UserTrainingRepository $userTrainingRepository)
    {
        $user = $this->getUser();
        $userTraining = $userTrainingRepository->findOneBy(['training' => $training, 'user' => $user]);
        $em = $this->getDoctrine()->getManager();
        if ($userTraining) {
            $userTraining->setIsInterestingForMe(false);
        } else {
            $userTraining = new UserTraining();
            $userTraining->setUser($user);
            $userTraining->setTraining($training);
            $userTraining->setIsInterestingForMe(false);
            $user->addUserTraining($userTraining);
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/uninterested_training/{id}", name="uninteresting_training", methods={"POST"})
     */
    public function uninterested_training(Training $training, UserTrainingRepository $userTrainingRepository)
    {
        $user = $this->getUser();
        $userTraining = $userTrainingRepository->findOneBy(['training' => $training, 'user' => $user]);
        if ($userTraining) {
            $userTraining->setIsUninterestingToMe(true);
            $userTraining->setIsInterestingForMe(false);
        } else {
            $userTraining = new UserTraining();
            $userTraining->setUser($user);
            $userTraining->setTraining($training);
            $userTraining->setIsUninterestingToMe(true);
            $userTraining->setIsInterestingForMe(false);
            $user->addUserTraining($userTraining);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($userTraining);
        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/notuninterested_training/{id}", name="notuninterested_training", methods={"POST"})
     */
    public function notuninterested_training(Training $training, UserTrainingRepository $userTrainingRepository)
    {
        $user = $this->getUser();
        $userTraining = $userTrainingRepository->findOneBy(['training' => $training, 'user' => $user]);
        $em = $this->getDoctrine()->getManager();
        if ($userTraining) {
            $userTraining->setIsUninterestingToMe(false);
        } else {
            $userTraining = new UserTraining();
            $userTraining->setUser($user);
            $userTraining->setTraining($training);
            $userTraining->setIsInterestingForMe(false);
            $user->addUserTraining($userTraining);
        }

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

        $affectedUsers = $userRepository->fetchAffectedUsers($skills);

        return $this->json(['result' => true, 'data' => $data, 'affected_users' => $affectedUsers], 200, ['Access-Control-Allow-Origin' => '*']);
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
        $errors = [];

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
                    $errors[] = $exception->getMessage();
                }
            }
        }

        $sentHistory = $position->getSentHistory();
        $dates = ($sentHistory && is_string($sentHistory) && !empty($sentHistory) && @unserialize($sentHistory)) ?
            unserialize($sentHistory) :
            [];
        $dates[] = (object) ['date' => $now, 'countUsers' => $countUsers, 'countErrors' => $countErrors, 'errors' => $errors];

        $position->setSentToAffectedUsersAt($now);
        $position->setSentHistory((@serialize($dates)) ? serialize($dates) : null);
        $position->setIsSentToAffectedUsers(true);
        $em->persist($position);
        $em->flush();

        return $this->json(['result' => true, 'countUsers' => $countUsers, 'countErrors' => $countErrors, 'errors' => $errors], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/get_logs_send_mails/{position_id}", name="get_logs_send_mails", methods={"GET"})
     */
    public function get_logs_send_mails($position_id, Request $request, UserRepository $userRepository, PositionRepository $positionRepository, TranslatorInterface $translator, MailerInterface $mailer)
    {

        $position = $positionRepository->find($position_id);
        if (!$position)
            return new JsonResponse(['message' => $translator->trans('no_position_found')], Response::HTTP_BAD_REQUEST);

        $sentHistory = $position->getSentHistory();
        $dates = ($sentHistory && is_string($sentHistory) && !empty($sentHistory) && @unserialize($sentHistory)) ?
            unserialize($sentHistory) :
            [];

        return $this->json(['result' => true, 'dates' => $dates], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/template_send_position_to_affected_users/{position_id}", name="template_send_position_to_affected_users", methods={"GET"})
     */
    public function template_send_position_to_affected_users($position_id, Request $request, UserRepository $userRepository, PositionRepository $positionRepository, TranslatorInterface $translator, MailerInterface $mailer)
    {
        $position = $positionRepository->find($position_id);
        if (!$position)
            return new JsonResponse(['message' => $translator->trans('no_position_found')], Response::HTTP_BAD_REQUEST);


        $address = $position->getLocation();
        if ($position->getLocation() && json_decode($position->getLocation())) {
            $address = json_decode($position->getLocation());
            if ($address && property_exists($address, 'title')) {
                $position->location_title = $address->title;
            }
            if ($address && property_exists($address, 'lat')) {
                $position->location_latitude = $address->lat;
            }
            if ($address && property_exists($address, 'lng')) {
                $position->location_longitude = $address->lng;
            }
        }

        $html = $this->render(
            'emails/position.html.twig',
            [
                'position' => $position,
            ])->getContent();


        return $this->json(['result' => true, 'html' => $html], 200, ['Access-Control-Allow-Origin' => '*']);
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
