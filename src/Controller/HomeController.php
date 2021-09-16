<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\Training;
use App\Entity\Position;
use App\Entity\UserActivity;
use App\Entity\UserSearch;
use App\Form\Type\UserType;
use App\Form\Type\RecruiterType;
use App\Form\Type\InstitutionType;
use App\Entity\TrainingSkill;
use App\Form\Type\PositionType;
use App\Form\Type\TrainingType;
use App\Repository\UserActivityRepository;
use App\Repository\UserRepository;
use App\Form\Type\UserPasswordType;
use App\Repository\SkillRepository;
use App\Repository\PositionRepository;
use App\Repository\TrainingRepository;
use App\Repository\OccupationRepository;
use App\Repository\OccupationSkillRepository;
use App\Repository\UserOccupationRepository;
use App\Repository\UserSearchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("", name="app_")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/{_locale<en|fr|pl|it>}/", name="home_root")
     * @Route("/{_locale<en|fr|pl|it>}/home", name="home")
     */
    public function index(Request $request): Response
    {
        return $this->render('front/home/search.html.twig');
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/search_results/{type}/{id}", name="search_results", requirements={"id"="\d+"})
     */
    public function searchResults(
        $type = null,
        $id = null,
        Request $request,
        OccupationRepository $occupationRepository,
        TrainingRepository $trainingRepository,
        SkillRepository $skillRepository,
        UserSearchRepository $userSearchRepository,
        TranslatorInterface $translator
    ): Response
    {
        $user = $this->getUser();
        $type_search = ($type) ? $type : $request->get('type_search'); // Type de recherche (occupation ou skill)
        $occupation_id = ($id) ? $id : $request->get('hidden_training_search_by_occupation');
        $skill_id = ($id) ? $id :$request->get('hidden_training_search_by_skill');
        $trainings = []; // Listes des formations
        $searchParams = []; // Parametres de recherche renvoyés à la vue
        $user = $this->getUser();
        $advanceSearchParams = $this->_get_advance_search_params($request);

        if ($type_search) {
            $search = new UserSearch();
            $search->setUser($user);
            $searchParams['type_search'] = $type_search;
            
            switch ($type_search) {
                case 'occupation':
                        if ($occupation_id) {
                            $occupation = $occupationRepository->findOneBy(['id' => $occupation_id]);
                            if (!$occupation) {
                                $this->addFlash('error', $translator->trans('flash.search_parameters_error'));
                                return $this->redirectToRoute('app_search_results');
                            }
                            $searchParams['name'] = $occupation->getPreferredLabel();
                            $searchParams['id'] = $occupation->getId();

                            $trainings = $trainingRepository->searchTrainingByOccupation($user, $occupation, $advanceSearchParams);
                            $search->setOccupation($occupation);
                            $search->setCountResults(count($trainings));
                        }
                    break;
                case 'skill':
                        if ($skill_id) {
                            $skill = $skillRepository->findOneBy(['id' => $skill_id]);
                            if (!$skill) {
                                $this->addFlash('error', $translator->trans('flash.search_parameters_error'));
                                return $this->redirectToRoute('app_search_results');
                            }
                            $searchParams['name'] = $skill->getPreferredLabel();
                            $searchParams['id'] = $skill->getId();
                            $trainings = $trainingRepository->searchTrainingBySkill($skill, $advanceSearchParams);
                            $search->setSkill($skill);
                            $search->setCountResults(count($trainings));
                        }
                    break;
                default:
                    $trainings = false;
                    break;
            }

            if ($user && $user->getIsSearchesKept()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($search);
                $em->flush();
            }

        }

        $searches = ($user) ? $userSearchRepository->getLast($user) : null;

        return $this->render(
            'front/search/index.html.twig',
            [
                'trainings' => $trainings,
                'search' => $searchParams,
                'searches' => $searches,
                'user' => $user,
                'requestParams' => $request->request->all(),
                'defaultMaxPrice' => $trainingRepository->getMaxPrice()
            ]
        );
    }

    protected function _get_advance_search_params(Request $request)
    {
        $params = [];
        $rp = $request->request->all();

        if (
            array_key_exists('distance', $rp) && !empty($rp['distance']) && is_numeric($rp['distance']) &&
            array_key_exists('city', $rp) && !empty($rp['city']) && preg_match('#^\{"lat":([\d.]+),"lng":([\d.]+)}$#', $rp['city'], $matches)
        ) {
            $params['distance'] = intval($rp['distance']);
            $params['location'] = ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        if (
            array_key_exists('minPrice', $rp) && !empty($rp['minPrice']) && is_numeric($rp['minPrice']) &&
            array_key_exists('maxPrice', $rp) && !empty($rp['maxPrice']) && is_numeric($rp['maxPrice']) &&
            array_key_exists('currency', $rp)
        ) {
            $params['minPrice'] = $rp['minPrice'];
            $params['maxPrice'] = $rp['maxPrice'];
            $params['currency'] = $rp['currency'];
        }

        if (
            array_key_exists('minDuration', $rp) && !empty($rp['minDuration']) && is_numeric($rp['minDuration']) &&
            array_key_exists('maxDuration', $rp) && !empty($rp['maxDuration']) && is_numeric($rp['maxDuration']) &&
            array_key_exists('unity', $rp)
        ) {
            switch($rp['unity']) {
                case 'hours' :
                    $params['minDuration']  = intval($rp['minDuration']) * 60 * 60;
                    $params['maxDuration']  = intval($rp['maxDuration']) * 60 * 60;
                    break;
                case 'days' :
                    $params['minDuration']  = intval($rp['minDuration']) * 60 * 60 * 24;
                    $params['maxDuration']  = intval($rp['maxDuration']) * 60 * 60 * 24;
                    break;
                case 'weeks' :
                    $params['minDuration']  = intval($rp['minDuration']) * 60 * 60 * 24 * 7;
                    $params['maxDuration']  = intval($rp['maxDuration']) * 60 * 60 * 24 * 7;
                    break;
                case 'months' :
                    $params['minDuration']  = intval($rp['minDuration']) * 60 * 60 * 24 * 30;
                    $params['maxDuration']  = intval($rp['maxDuration']) * 60 * 60 * 24 * 30;
                    break;
            }
            $params['unity'] = $rp['unity'];
        }

        if (array_key_exists('isOnline', $rp) && !empty($rp['isOnline']))
            $params['isOnline'] = (bool) ($rp['isOnline'] === true || $rp['isOnline'] === 'on');

        if (array_key_exists('isOnlineMonitored', $rp) && !empty($rp['isOnlineMonitored']))
            $params['isOnlineMonitored'] = (bool) ($rp['isOnlineMonitored'] === true || $rp['isOnlineMonitored'] === 'on');

        if (array_key_exists('isPresential', $rp) && !empty($rp['isPresential']))
            $params['isPresential'] = (bool) ($rp['isPresential'] === true || $rp['isPresential'] === 'on');

        if (array_key_exists('excludeTraining', $rp) && !empty($rp['excludeTraining']))
            $params['excludeWithoutDescription'] = (bool) ($rp['excludeTraining'] === true || $rp['excludeTraining'] === 'on');

        if (array_key_exists('specifiedDuration', $rp) && !empty($rp['specifiedDuration']))
            $params['excludeWithoutDuration'] = (bool) ($rp['specifiedDuration'] === true || $rp['specifiedDuration'] === 'on');

        if (array_key_exists('startAt', $rp) && !empty($rp['startAt']))
            $params['startAt'] = $rp['startAt'];
        if (array_key_exists('endAt', $rp) && !empty($rp['endAt']))
            $params['endAt'] = $rp['endAt'];

        return $params;
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/account/{tab}", name="account")
     */
    public function account(
        $tab = 'personal_informations',
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder,
        SkillRepository $skillRepository,
        UserRepository $userRepository,
        OccupationSkillRepository $occupationSkillRepository
    ):Response
    {
        if ($this->isGranted(User::ROLE_INSTITUTION))
        return $this->redirectToRoute('app_home');

        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, ['is_personal' => true]);
        $passwordForm = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return new Response((string)$errors, 400);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success', $translator->trans('Updated data'));
            
            return $this->redirectToRoute('app_account');
        } else if ($passwordForm->isSubmitted()) {
            if (!$passwordForm->isValid()) {
                $errors = $validator->validate($user);
                return new Response((string)$errors, 400);
            }

            $tab = 'change_password';

            //$data = $request->request->all('user_password');
            //$result = $passwordEncoder->isPasswordValid($user, 'test');
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data'));
        }

        $currentOccupations = $user->getCurrentOccupations();
        $desiredOccupations = $user->getDesiredOccupations();
        $previousOccupations = $user->getPreviousOccupations();

        $currentOccupationsInput = [];
        $desiredOccupationsInput = [];
        $previousOccupationsInput = [];

        if ($currentOccupations && count($currentOccupations) > 0) {
            foreach ($currentOccupations as $k => $currentOccupation) {
                $currentOccupation->skills = new ArrayCollection($occupationSkillRepository->findBy(['occupation' => $currentOccupation->getOccupation()]));
                array_push($currentOccupationsInput, $currentOccupation->getOccupation()->getId());
            }
        }
        if ($desiredOccupations && count($desiredOccupations) > 0) {
            foreach ($desiredOccupations as $k => $desiredOccupation) {
                $desiredOccupation->skills = new ArrayCollection($occupationSkillRepository->findBy(['occupation' => $desiredOccupation->getOccupation()]));
                array_push($desiredOccupationsInput, $desiredOccupation->getOccupation()->getId());
            }
        }
        if ($previousOccupations && count($previousOccupations) > 0) {
            foreach ($previousOccupations as $k => $previousOccupation) {
                $previousOccupation->skills = new ArrayCollection($occupationSkillRepository->findBy(['occupation' => $previousOccupation->getOccupation()]));
                array_push($previousOccupationsInput, $previousOccupation->getOccupation()->getId());
            }
        }

        return $this->render(
            'front/account/index.html.twig',
            [
                'user' => $user,
                'currentOccupations' => $currentOccupations,
                'desiredOccupations' => $desiredOccupations,
                'previousOccupations' => $previousOccupations,
                'currentOccupationsInput' => $currentOccupationsInput,
                'desiredOccupationsInput' => $desiredOccupationsInput,
                'previousOccupationsInput' => $previousOccupationsInput,
                'form' => $form->createView(),
                'password_form' => $passwordForm->createView(),
                'related_skills' => $skillRepository->getByOccupationAndTraining($user),
                'tab' => $tab
            ]
        );
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/search_history", name="search_history")
     */
    public function search_history(UserSearchRepository $userSearchRepository)
    {
        $user = $this->getUser();
        $searches = $userSearchRepository->getAll($user);

        return $this->render('front/search/history.html.twig',
             [
                 'user'   => $user,
                 'searches' => $searches
             ]
        );
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/institution/{tab}", name="institution")
     */
    public function institution(
        $tab = 'personal_informations',
        Request $request,
        TrainingRepository $trainingRepository,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response
    {
        if (!$this->isGranted(User::ROLE_INSTITUTION))
            return $this->redirectToRoute('app_home');

        $user = $this->getUser();

        $form = $this->createForm(InstitutionType::class, $user);
        $passwordForm = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data'));

            return $this->redirectToRoute('app_institution');
            
        } else if ($passwordForm->isSubmitted()) {
            if (!$passwordForm->isValid()) {
                $errors = $validator->validate($user);
                return new Response((string) $errors, 400);
            }

            $tab = 'change_password';

            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data'));
        }

        $tab = (array_key_exists('tab_institution_silkc', $_COOKIE)) ? $_COOKIE['tab_institution_silkc'] : ($tab ? $tab : false);
        setcookie('tab_institution_silkc', "", time() - 3600, "/");

        $trainings = $trainingRepository->findBy(['user' => $user]);
        return $this->render('front/institutional/index.html.twig',
            [
                'trainings'   => $trainings,
                'form' => $form->createView(),
                'password_form' => $passwordForm->createView(),
                'tab' => $tab
            ]
        );
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/recruiter/{tab}", name="recruiter")
     */
    public function recruiter(
        $tab = 'personal_informations',
        Request $request,
        PositionRepository $positionRepository,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response
    {
        if (!$this->isGranted(User::ROLE_RECRUITER))
            return $this->redirectToRoute('app_home');

        $user = $this->getUser();

        $form = $this->createForm(RecruiterType::class, $user);
        $passwordForm = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data'));

            return $this->redirectToRoute('app_recruiter');

        } else if ($passwordForm->isSubmitted()) {
            if (!$passwordForm->isValid()) {
                $errors = $validator->validate($user);
                return new Response((string) $errors, 400);
            }

            $tab = 'change_password';

            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data'));
        }

        $tab = (array_key_exists('tab_institution_silkc', $_COOKIE)) ? $_COOKIE['tab_institution_silkc'] : ($tab ? $tab : false);
        setcookie('tab_institution_silkc', "", time() - 3600, "/");

        $positions = $positionRepository->findBy(['user' => $user]);
        return $this->render(
            'front/recruiter/index.html.twig',
            [
                'positions'   => $positions,
                'form' => $form->createView(),
                'password_form' => $passwordForm->createView(),
                'tab' => $tab
            ]
        );
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/training/create", name="training_create")
     */
    public function training_create(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, SkillRepository $skillRepository): Response
    {
        $user = $this->getUser();
        $training = new Training();

        $form = $this->createForm(TrainingType::class, $training, ['is_user' => !$this->isGranted(User::ROLE_INSTITUTION)]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($training);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }
            
            $em = $this->getDoctrine()->getManager();
            $oldTrainingSkills = $training->getTrainingSkills();
            $newTrainingSkills = new ArrayCollection();
            if (
                $request->request->get('hidden_trainingSkills') !== NULL &&
                @json_decode($request->request->get('hidden_trainingSkills')) !== NULL
            ) {
                $skills = json_decode($request->request->get('hidden_trainingSkills'));
                if (property_exists($skills, 'acquired')) {
                    foreach ($skills->acquired as $skillId) {
                        $skill = $skillRepository->findOneBy(['id' => $skillId]);
                        if (!$skill)
                            continue;

                        $trainingSkill = new TrainingSkill();
                        $trainingSkill->setSkill($skill);
                        $trainingSkill->setTraining($training);
                        $trainingSkill->setIsToAcquire(true);
                        $em->persist($trainingSkill);
                        $training->addTrainingSkill($trainingSkill);
                        $newTrainingSkills->add($trainingSkill);
                    }
                }

                if (property_exists($skills, 'required')) {
                    foreach ($skills->required as $skillId) {
                        $skill = $skillRepository->findOneBy(['id' => $skillId]);
                        if (!$skill)
                            continue;

                        $trainingSkill = new TrainingSkill();
                        $trainingSkill->setSkill($skill);
                        $trainingSkill->setTraining($training);
                        $trainingSkill->setIsRequired(true);
                        $em->persist($trainingSkill);
                        $training->addTrainingSkill($trainingSkill);
                        $newTrainingSkills->add($trainingSkill);
                    }
                }
            }
            foreach ($oldTrainingSkills as $trainingSkill) {
                if (!$newTrainingSkills->contains($trainingSkill))
                    $training->removeTrainingSkill($trainingSkill);
            }

            if ($training->getUser() === null)
                $training->setUser($user);
            $training->setCreator($user);
            // Si l'utilisateur est un admin ou institution, la formation est validée par défaut
            $training->setIsValidated($this->isGranted(User::ROLE_INSTITUTION));
            // S'il s'agit d'une création par un utilisateur, on lui associe la formation
            if (!$this->isGranted(User::ROLE_INSTITUTION))
                $user->addTraining($training);

            $em->persist($training);
            $em->flush();

            $this->addFlash('success', $translator->trans('The training was created'));

            return $this->redirectToRoute('app_training_edit', array('id' => $training->getId()));
        }

        setcookie('tab_institution_silkc', 2, time() + 86400, "/");

        return $this->render('front/institutional/training_create.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/training/edit/{id}", name="training_edit")
     */
    public function edit(Training $training, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, SkillRepository $skillRepository, TrainingRepository $trainingRepository):Response
    {
        $form = $this->createForm(
            TrainingType::class,
            $training,
            [
                'is_user' => !$this->isGranted(User::ROLE_INSTITUTION),
                'can_validate' => ($this->isGranted(User::ROLE_ADMIN) && $training->getIsValidated() != true),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($training);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }
            
            $em = $this->getDoctrine()->getManager();
            $oldTrainingSkills = $training->getTrainingSkills();
            $newTrainingSkills = new ArrayCollection();
            if (
                $request->request->get('hidden_trainingSkills') !== NULL &&
                @json_decode($request->request->get('hidden_trainingSkills')) !== NULL
            ) {
                $skills = json_decode($request->request->get('hidden_trainingSkills'));
                if (property_exists($skills, 'acquired')) {
                    foreach ($skills->acquired as $skillId) {
                        $skill = $skillRepository->findOneBy(['id' => $skillId]);
                        if (!$skill)
                            continue;

                        $trainingSkill = new TrainingSkill();
                        $trainingSkill->setSkill($skill);
                        $trainingSkill->setTraining($training);
                        $trainingSkill->setIsToAcquire(true);
                        $training->addTrainingSkill($trainingSkill);
                        $newTrainingSkills->add($trainingSkill);
                        $em->persist($trainingSkill);
                    }
                }

                if (property_exists($skills, 'required')) {
                    foreach ($skills->required as $skillId) {
                        $skill = $skillRepository->findOneBy(['id' => $skillId]);
                        if (!$skill)
                            continue;

                        $trainingSkill = new TrainingSkill();
                        $trainingSkill->setSkill($skill);
                        $trainingSkill->setTraining($training);
                        $trainingSkill->setIsRequired(true);
                        $training->addTrainingSkill($trainingSkill);
                        $newTrainingSkills->add($trainingSkill);
                        $em->persist($trainingSkill);
                    }
                }
            }

            foreach ($oldTrainingSkills as $trainingSkill) {
                if (!$newTrainingSkills->contains($trainingSkill))
                    $training->removeTrainingSkill($trainingSkill);
            }

            if ($form->get('save_and_validate')->isClicked()) {
                $training->setIsValidated(true);
                $training->setValidatedAt(new \DateTime());
                $em->persist($training);
                $em->flush();

                $this->addFlash('success', $translator->trans('The training has been updated'));

                return $this->redirectToRoute('admin_home', ['tab' => 'tasks']);
            }
            else {
                $em->persist($training);
                $em->flush();

                $this->addFlash('success', $translator->trans('The training has been updated'));

                return $this->redirectToRoute('app_training_edit', ['id' => $training->getId()]);
            }
        }

        setcookie('tab_institution_silkc', 2, time() + 86400, "/");

        return $this->render('front/institutional/training_create.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
            'training' => $training,
        ]);
    }

    /**
     * @Route("/training/duplicate/{id}", name="training_duplicate")
     */
    public function training_duplicate(Training $training, SkillRepository $skillRepository, TrainingRepository $trainingRepository, TranslatorInterface $translator): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $newTraining = clone $training;

        $trainingSkills = new ArrayCollection();
        if ($training->getTrainingSkills()) {
            foreach ($training->getTrainingSkills() as $trainingSkill) {
                $newTrainingSkill = new TrainingSkill();
                $newTrainingSkill->setTraining($newTraining);
                $newTrainingSkill->setSkill($trainingSkill->getSkill());
                $newTrainingSkill->setIsRequired($trainingSkill->getIsRequired());
                $newTrainingSkill->setIsToAcquire($trainingSkill->getIsToAcquire());

                $trainingSkills->add($newTrainingSkill);

                $em->persist($newTrainingSkill);
            }
        }
        $newTraining->setTrainingSkills($trainingSkills);

        if ($newTraining->getUser() === null)
        $newTraining->setUser($user);
        $newTraining->setCreator($user);
        $newTraining->setName($newTraining->getName() . $translator->trans('training_duplicate_suffix'));
        
        // Si l'utilisateur est un admin ou institution, la formation est validée par défaut
        $newTraining->setIsValidated($this->isGranted(User::ROLE_INSTITUTION));
        // S'il s'agit d'une création par un utilisateur, on lui associe la formation
        if (!$this->isGranted(User::ROLE_INSTITUTION))

        $user->addTraining($newTraining);
        
        $em->persist($newTraining);
        $em->flush();

        $this->addFlash('success', $translator->trans('duplicate_training_success'));

        return $this->redirectToRoute('app_training_edit', ['id' => $newTraining->getId()]);
    }

    /**
     * @Route("/position/create", name="position_create")
     */
    public function position_create(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, SkillRepository $skillRepository): Response
    {
        $user = $this->getUser();
        $position = new Position();

        $form = $this->createForm(PositionType::class, $position, ['is_user' => !$this->isGranted(User::ROLE_RECRUITER)]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($position);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $em = $this->getDoctrine()->getManager();
            $oldSkills = $position->getSkills();
            if (
                $request->request->get('hidden_positionSkills') !== NULL &&
                @json_decode($request->request->get('hidden_positionSkills')) !== NULL
            ) {
                $skills = json_decode($request->request->get('hidden_positionSkills'));
                foreach ($skills as $skillId) {
                    $skill = $skillRepository->findOneBy(['id' => $skillId]);
                    if (!$skill)
                        continue;

                    $position->addSkill($skill);
                }
            }
            foreach ($oldSkills as $oldSkill) {
                if (!$position->getSkills()->contains($oldSkill))
                    $position->removeSkill($oldSkill);
            }

            if ($position->getUser() === null)
                $position->setUser($user);
            $position->setCreator($user);
            // Si l'utilisateur est un admin ou institution, la formation est validée par défaut
            $position->setIsValidated($this->isGranted(User::ROLE_RECRUITER));
            // S'il s'agit d'une création par un utilisateur, on lui associe la formation
            if (!$this->isGranted(User::ROLE_RECRUITER))
                $user->addTraining($position);

            $em->persist($position);
            $em->flush();

            $this->addFlash('success', $translator->trans('The position was created'));

            return $this->redirectToRoute('app_position_edit', array('id' => $position->getId()));
        }

        setcookie('tab_recruiter_silkc', 2, time() + 86400, "/");

        return $this->render('front/recruiter/position_create.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/position/edit/{id}", name="position_edit")
     */
    public function edit_position(Position $position, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, SkillRepository $skillRepository, PositionRepository $positionRepository):Response
    {
        $form = $this->createForm(PositionType::class, $position, ['is_user' => !$this->isGranted(User::ROLE_RECRUITER)]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($position);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $em = $this->getDoctrine()->getManager();
            $oldSkills = $position->getSkills();
            $newSkills = new ArrayCollection();
            if (
                $request->request->get('hidden_positionSkills') !== NULL &&
                @json_decode($request->request->get('hidden_positionSkills')) !== NULL
            ) {
                $skills = json_decode($request->request->get('hidden_positionSkills'));
                foreach ($skills as $skillId) {
                    $skill = $skillRepository->findOneBy(['id' => $skillId]);
                    if (!$skill)
                        continue;

                    $newSkills->add($skill);
                    $position->addSkill($skill);
                }
            }

            foreach ($oldSkills as $oldSkill) {
                if (!$newSkills->contains($oldSkill))
                    $position->removeSkill($oldSkill);
            }

            $em->persist($position);
            $em->flush();

            $this->addFlash('success', $translator->trans('The position has been updated'));

            return $this->redirectToRoute('app_position_edit', ['id' => $position->getId()]);
        }

        setcookie('tab_recruiter_silkc', 2, time() + 86400, "/");

        return $this->render('front/recruiter/position_create.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
            'position' => $position,
        ]);
    }

    /**
     * @Route("/position/duplicate/{id}", name="position_duplicate")
     */
    public function position_duplicate(Position $position, SkillRepository $skillRepository, PositionRepository $positionRepository, TranslatorInterface $translator): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $newPosition = clone $position;

        if ($position->getSkills()) {
            foreach ($position->getSkills() as $skill) {
                $newPosition->add($skill);
            }
        }

        if ($newPosition->getUser() === null)
            $newPosition->setUser($user);
        $newPosition->setCreator($user);
        $newPosition->setName($newPosition->getName() . $translator->trans('position_duplicate_suffix'));

        // Si l'utilisateur est un admin ou institution, la formation est validée par défaut
        $newPosition->setIsValidated($this->isGranted(User::ROLE_RECRUITER));
        // S'il s'agit d'une création par un utilisateur, on lui associe la formation
        if (!$this->isGranted(User::ROLE_RECRUITER))
            $user->addPosition($newPosition);

        $em->persist($newPosition);
        $em->flush();

        $this->addFlash('success', $translator->trans('duplicate_position_success'));

        return $this->redirectToRoute('app_training_edit', ['id' => $newPosition->getId()]);
    }

    /**
     * @Route("/set_score", name="set_score")
     */
    public function set_score(Request $request, TrainingRepository $trainingRepository, UserActivityRepository $userActivityRepository)
    {
        $user = $this->getUser();
        $training_id = $request->query->get('id');
        $score = $request->query->get('score', null);

        if ($request->getMethod() !== Request::METHOD_GET || !$training_id || !$score)
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        $training = $trainingRepository->findOneBy(['id' => intval($training_id)]);
        if (!$training)
            return new JsonResponse(['message' => 'Training unknown'], Response::HTTP_BAD_REQUEST);

        $userActivity = $userActivityRepository->findOneBy(['user' => $user, 'training' => $training]);
        if (!$userActivity) {
            $userActivity = new UserActivity();
            $userActivity->setTraining($training);
            $userActivity->setUser($user);
        }
        $userActivity->setUpdatedAt(new \DateTime());
        $userActivity->setScore(intval($score));

        $em = $this->getDoctrine()->getManager();
        $em->persist($userActivity);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }
}
