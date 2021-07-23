<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\Training;
use App\Entity\UserActivity;
use App\Entity\UserSearch;
use App\Form\Type\UserType;
use App\Entity\TrainingSkill;
use App\Form\Type\TrainingType;
use App\Repository\UserActivityRepository;
use App\Repository\UserRepository;
use App\Form\Type\UserPasswordType;
use App\Repository\SkillRepository;
use App\Repository\TrainingRepository;
use App\Repository\OccupationRepository;
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
     * @Route("/")
     * @Route("/home", name="home")
     */
    public function index(): Response
    {
        return $this->render('front/home/search.html.twig');
    }

    /**
     * @Route("/search_results/{type}/{id}", name="search_results", requirements={"id"="\d+"})
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
                            $trainings = $trainingRepository->searchTrainingByOccupation($user, $occupation);
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
                            $trainings = $trainingRepository->searchTrainingBySkill($skill);
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
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/account/{tab}", name="account")
     */
    public function account(
        $tab = 'personal_informations',
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder,
        SkillRepository $skillRepository,
        UserRepository $userRepository
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

        return $this->render(
            'front/account/index.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                'password_form' => $passwordForm->createView(),
                'related_skills' => $skillRepository->getByOccupationAndTraining($user),
                'tab' => $tab
            ]
        );
    }

    /**
     * @Route("/search_history", name="search_history")
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
     * @Route("/institution/{tab}", name="institution")
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

        $form = $this->createForm(UserType::class, $user, ['is_personal' => false]);
        $passwordForm = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

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

            //$data = $request->request->all('user_password');
            //$result = $passwordEncoder->isPasswordValid($user, 'test');
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data'));
        }

        $tab = (array_key_exists('tab_institution_silkc', $_COOKIE)) ? $_COOKIE['tab_institution_silkc'] : $tab ? $tab : false;
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
     * @Route("/training/create", name="training_create")
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
     * @Route("/training/edit/{id}", name="training_edit")
     */
    public function edit(Training $training, Request $request, ValidatorInterface $validator, TranslatorInterface $translator, SkillRepository $skillRepository, TrainingRepository $trainingRepository):Response
    {
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

            $em->persist($training);
            $em->flush();

            $this->addFlash('success', $translator->trans('The training has been updated'));

            return $this->redirectToRoute('app_training_edit', ['id' => $training->getId()]);
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
    public function training_duplicate(Training $training, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $newTraining = clone $training;

        if ($newTraining->getUser() === null)
            $newTraining->setUser($user);
        $newTraining->setCreator($user);
        $newTraining->setName($newTraining->getName() . $translator->trans('training_duplicate_suffix'));
        // Si l'utilisateur est un admin ou institution, la formation est validée par défaut
        $newTraining->setIsValidated($this->isGranted(User::ROLE_INSTITUTION));
        // S'il s'agit d'une création par un utilisateur, on lui associe la formation
        if (!$this->isGranted(User::ROLE_INSTITUTION))
            $user->addTraining($newTraining);

        $em = $this->getDoctrine()->getManager();
        $em->persist($newTraining);
        $em->flush();

        $this->addFlash('success', $translator->trans('duplicate_training_success'));

        return $this->redirectToRoute('app_training_edit', ['id' => $newTraining->getId()]);
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
