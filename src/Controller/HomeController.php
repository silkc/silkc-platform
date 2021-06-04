<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\Training;
use App\Form\Type\UserType;
use App\Entity\TrainingSkill;
use App\Form\Type\TrainingType;
use App\Repository\UserRepository;
use App\Form\Type\UserPasswordType;
use App\Repository\SkillRepository;
use App\Repository\TrainingRepository;
use App\Repository\OccupationRepository;
use App\Repository\UserOccupationRepository;
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
     * @Route("/", name="home")
     */
    public function index(): Response
    {

        return $this->render('front/home/search.html.twig');
    }

    /**
     * @Route("/search_results", name="search_results")
     */
    public function searchResults(Request $request, OccupationRepository $occupationRepository, TrainingRepository $trainingRepository, SkillRepository $skillRepository): Response
    {
        $user = $this->getUser();
        $type_search = $request->get('type_search'); // Type de recherche (occupation ou skill)
        $trainings = []; // Listes des formations
        $search = []; // Parametres de recherche renvoyés à la vue
        
        if ($type_search) {
            $search['type_search'] = $type_search;
            
            switch ($type_search) {
                case 'occupation':
                        $occupation_id = $request->get('hidden_training_search_by_occupation');
                        $occupation_name = $request->get('training_search_by_occupation');
                        if ($occupation_id && $occupation_name) {
                            $search['id'] = $occupation_id;
                            $search['name'] = $occupation_name;
                            $occupation = $occupationRepository->findOneBy(['id' => $occupation_id]);
                            $trainings = $trainingRepository->searchTrainingByOccupation($user, $occupation);
                        }
                    break;
                case 'skill':
                        $skill_id = $request->get('hidden_training_search_by_skill');
                        $skill_name = $request->get('training_search_by_skill');
                        if ($skill_id && $skill_name) {
                            $search['id'] = $skill_id;
                            $search['name'] = $skill_name;
                            $skill = $skillRepository->findOneBy(['id' => $skill_id]);
                            $trainings = $trainingRepository->searchTrainingBySkill($skill);
                        }
                    break;
                default:
                    $trainings = false;
                    break;
            }
        }

        return $this->render('front/search/index.html.twig', ['trainings' => $trainings, 'search' => $search]);
    }

    /**
     * @Route("/account", name="account")
     */
    public function account(
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
            $em = $this->getDoctrine()->getManager();

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                die('error');
                return new Response((string)$errors, 400);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success', $translator->trans('Updated data', [], 'admin'));
            
            return $this->redirectToRoute('app_account');
        } else if ($passwordForm->isSubmitted()) {
            if (!$passwordForm->isValid()) {
                $errors = $validator->validate($user);
                dd($errors);
            }

            //$data = $request->request->all('user_password');
            //$result = $passwordEncoder->isPasswordValid($user, 'test');
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data', [], 'admin'));
        }

        return $this->render(
            'front/account/index.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                'password_form' => $passwordForm->createView(),
                'related_skills' => $skillRepository->getByOccupationAndTraining($user)
            ]
        );
    }

    /**
     * @Route("/institution", name="institution")
     */
    public function institution(
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
                die('error');
                return new Response((string)$errors, 400);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data', [], 'admin'));

            return $this->redirectToRoute('app_institution');
        } else if ($passwordForm->isSubmitted()) {
            if (!$passwordForm->isValid()) {
                $errors = $validator->validate($user);
                dd($errors);
            }

            //$data = $request->request->all('user_password');
            //$result = $passwordEncoder->isPasswordValid($user, 'test');
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Updated data', [], 'admin'));
        }

        $tab = (array_key_exists('tab_institution_silkc', $_COOKIE)) ? $_COOKIE['tab_institution_silkc'] : false;
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

        $form = $this->createForm(TrainingType::class, $training);
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

            $training->setUser($user);
            $em->persist($training);
            $em->flush();

            $this->addFlash('success', $translator->trans('The training was created', [], 'admin'));

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
        $form = $this->createForm(TrainingType::class, $training);
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

            $this->addFlash('success', $translator->trans('The training has been updated', [], 'admin'));

            return $this->redirectToRoute('app_training_edit', ['id' => $training->getId()]);
        }

        setcookie('tab_institution_silkc', 2, time() + 86400, "/");

        return $this->render('front/institutional/training_create.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
            'training' => $training,
        ]);
    }
}
