<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\Training;
use App\Entity\Occupation;
use App\Form\Type\UserPasswordType;
use App\Form\Type\RecruiterType;
use App\Form\Type\InstitutionType;
use App\Form\Type\UserType;
use App\Repository\NotificationRepository;
use App\Repository\SkillRepository;
use App\Repository\OccupationRepository;
use App\Repository\TrainingRepository;
use App\Repository\TrainingSkillRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
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
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/{_locale<en|fr|pl|it>}/home/{tab}", name="home")
     */
    public function index(
        $tab = 'home',
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder,
        SkillRepository $skillRepository,
        UserRepository $userRepository,
        OccupationRepository $occupationRepository,
        NotificationRepository $notificationRepository,
        TrainingRepository $trainingRepository
    ): Response
    {
        if (!$this->isGranted(User::ROLE_ADMIN))
            return $this->redirectToRoute('app_home');

        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, ['is_personal' => true]);
        $passwordForm = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        $passwordForm->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $tab = 'personal_informations';

            $em = $this->getDoctrine()->getManager();

            $errors = $validator->validate($user);
            if (count($errors) > 0)
                return new Response((string)$errors, 400);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('updated_data'));

            //return $this->redirectToRoute('admin_home');
        } else if ($passwordForm->isSubmitted()) {
            if (!$passwordForm->isValid()) {
                $errors = $validator->validate($user);
                $errorsString = (string) $errors;
                return new Response($errorsString);
            }

            $tab = 'change_password';

            //$data = $request->request->all('user_password');
            //$result = $passwordEncoder->isPasswordValid($user, 'test');
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('updated_data'));
        }

        return $this->render(
            'admin/index.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                ////'notifications' => $notificationRepository->findBy(['isRead' => false]),
                'to_validated_trainings' => $trainingRepository->findBy(['isValidated' => false, 'isRejected' => false]),
                ////'to_validated_institutions' => $userRepository->findBy(['isValidated' => false]),
                ////'trainings' => $trainingRepository->findAll(),
                ////'skills' => $skillRepository->findAll(),
                ////'occupations' => $occupationRepository->findAll(),
                'password_form' => $passwordForm->createView(),
                //'users' => $userRepository->findByRole('ROLE_USER'),
                ////'users' => $userRepository->findAll(),
                'tab' => $tab
                //'related_skills' => $skillRepository->getByOccupationAndTraining($user)
            ]
        );
    }
    
    /**
     * @Route("/users", name="users")
     */
    public function users(
        UserRepository $userRepository
    ): Response
    {
        if (!$this->isGranted(User::ROLE_ADMIN))
            return $this->json(
                [
                    'result' => false,
                ],
                200,
                ['Access-Control-Allow-Origin' => '*']
            );

        $user = $this->getUser();

        return $this->json(
            [
                'result' => true,
                'users' => $userRepository->findAll(),
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }
    
    /**
     * @Route("/trainings", name="trainings")
     */
    public function trainings(
        TrainingRepository $trainingRepository
    ): Response
    {
        if (!$this->isGranted(User::ROLE_ADMIN))
            return $this->json(
                [
                    'result' => false,
                ],
                200,
                ['Access-Control-Allow-Origin' => '*']
            );

        $user = $this->getUser();

        return $this->json(
            [
                'result' => true,
                'trainings' => $trainingRepository->findAll()
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }
    
    /**
     * @Route("/skills/{lang}", name="skills")
     */
    public function skills(
        $lang = 'en',
        SkillRepository $skillRepository
    ): Response
    {
        if (!$this->isGranted(User::ROLE_ADMIN))
            return $this->json(
                [
                    'result' => false,
                ],
                200,
                ['Access-Control-Allow-Origin' => '*']
            );

        $user = $this->getUser();

        return $this->json(
            [
                'result' => true,
                'skills' => $skillRepository->findAllByLocale($lang),
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }
    
    /**
     * @Route("/occupations/{lang}", name="occupations")
     */
    public function occupations(
        $lang = 'en',
        OccupationRepository $occupationRepository
    ): Response
    {
        if (!$this->isGranted(User::ROLE_ADMIN))
            return $this->json(
                [
                    'result' => false,
                ],
                200,
                ['Access-Control-Allow-Origin' => '*']
            );

        $user = $this->getUser();

        return $this->json(
            [
                'result' => true,
                'occupations' => $occupationRepository->findAllByLocale($lang),
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }
    
    /**
     * @Route("/tasks", name="tasks")
     */
    public function tasks(
        TrainingRepository $trainingRepository
    ): Response
    {
        if (!$this->isGranted(User::ROLE_ADMIN))
            return $this->json(
                [
                    'result' => false,
                ],
                200,
                ['Access-Control-Allow-Origin' => '*']
            );

        $user = $this->getUser();

        return $this->json(
            [
                'result' => true,
                'to_validated_trainings' => $trainingRepository->findBy(['isValidated' => false, 'isRejected' => false]),
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }
    
    /**
     * @Route("/notifications", name="notifications")
     */
    public function notifications(
        NotificationRepository $notificationRepository
    ): Response
    {
        if (!$this->isGranted(User::ROLE_ADMIN))
            return $this->json(
                [
                    'result' => false,
                ],
                200,
                ['Access-Control-Allow-Origin' => '*']
            );

        return $this->json(
            [
                'result' => true,
                'notifications' => $notificationRepository->findBy(['isRead' => false]),
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/create_user/{type}", name="create_user", requirements={"type"="user|institution|recruiter"}, methods={"GET", "POST"})
     */
    public function create_user(
        string $type = 'user',
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $user = new User();
        if ($type === 'recruiter') {
            $user->setRoles(['ROLE_RECRUITER']);
            $form = $this->createForm(RecruiterType::class, $user, ['is_personal' => true, 'by_admin' => true, 'enable_password' => true]);
        } else if ($type === 'institution') {
            $user->setRoles(['ROLE_INSTITUTION']);
            $form = $this->createForm(InstitutionType::class, $user, ['is_personal' => true, 'by_admin' => true, 'enable_password' => true]);
        } else {
            $user->setRoles(['ROLE_USER']);
            $form = $this->createForm(UserType::class, $user, ['is_personal' => true, 'by_admin' => true, 'enable_password' => true]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $errors = $validator->validate($user);
            if (count($errors) > 0)
                return new Response((string) $errors, 400);

            $createdAt = new \DateTime('now');
            $password = $user->getPassword();
            $password = $passwordEncoder->encodePassword($user, $password);
            $apiToken = base64_encode(sha1($createdAt->format('Y-m-d H:i:s').$password, true));

            $user->setTokenCreatedAt($createdAt);
            $user->setCreatedAt($createdAt);
            $user->setApiToken($apiToken);
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('updated_data'));
            return $this->redirectToRoute('admin_edit_user', ['id' => $user->getId()]);
        }

        return $this->render(
            'admin/edit_user.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                'role' => $role
            ]
        );
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/edit_user/{id}", name="edit_user", methods={"GET", "POST"})
     */
    public function edit_user(
        User $user,
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        if ($user->getRoles() !== null && in_array(User::ROLE_RECRUITER, $user->getRoles()))
            $form = $this->createForm(RecruiterType::class, $user, ['is_personal' => true, 'by_admin' => true]);
        else if ($user->getRoles() !== null && in_array(User::ROLE_INSTITUTION, $user->getRoles()))
            $form = $this->createForm(InstitutionType::class, $user, ['is_personal' => true, 'by_admin' => true]);
        else
            $this->createForm(UserType::class, $user, ['is_personal' => true, 'by_admin' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $errors = $validator->validate($user);
            if (count($errors) > 0)
                return new Response((string) $errors, 400);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('updated_data'));
        }

        return $this->render(
            'admin/edit_user.html.twig',
            [
                'user' => $user,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/read", name="read")
     */
    public function read(
        Request $request,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder,
        SkillRepository $skillRepository,
        UserRepository $userRepository,
        NotificationRepository $notificationRepository,
        TrainingRepository $trainingRepository
    ): Response
    {
        
    }

    /**
     * @Route("/get_skill_related_trainings/{id}", name="get_skill_related_trainings")
     */
    public function get_skill_related_trainings(
        Skill $skill,
        Request $request,
        TrainingSkillRepository $trainingSkillRepository,
        TrainingRepository $trainingRepository
    )
    {
        $trainingSkills = $trainingSkillRepository->findBy(['skill' => $skill, 'isToAcquire' => true]);

        $trainings = new ArrayCollection();
        if ($trainingSkills) {
            foreach ($trainingSkills as $trainingSkill) {
                $trainings->add($trainingSkill->getTraining());
            }
        }

        return $this->json(
            [
                'result' => true,
                'skill' => $skill,
                'trainings' => $trainings
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/get_occupation_related_trainings/{id}", name="get_occupation_related_trainings")
     */
    public function get_occupation_related_trainings(
        Occupation $occupation,
        Request $request,
        TrainingRepository $trainingRepository
    )
    {
        $trainings = $trainingRepository->findBy(['occupation' => $occupation]);

        return $this->json(
            [
                'result' => true,
                'occupation' => $occupation,
                'trainings' => $trainings
            ],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/delete_training/{id}", name="delete_training", methods="POST")
     */
    public function delete_training(Training $training)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($training);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/approve_training/{id}", name="approve_training", methods="POST")
     */
    public function approve_training(Training $training)
    {
        $training->setIsRejected(false);
        $training->setIsValidated(true);
        $training->setValidatedAt(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($training);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/reject_training/{id}", name="reject_training", methods="POST")
     */
    public function reject_training(Training $training)
    {
        $training->setIsValidated(false);
        $training->setIsRejected(true);
        $training->setRejectedAt(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($training);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/suspend_user/{id}", name="suspend_user", methods="POST")
     */
    public function suspend_user(User $user)
    {
        $user->setIsSuspended(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/unsuspend_user/{id}", name="unsuspend_user", methods="POST")
     */
    public function unsuspend_user(User $user)
    {
        $user->setIsSuspended(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/approve_user/{id}", name="approve_user", methods="POST")
     */
    public function approve_user(User $user)
    {
        $user->setIsValidated(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/reject_user/{id}", name="reject_user", methods="POST")
     */
    public function reject_user(User $user)
    {
        $user->setIsValidated(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/delete_user/{id}", name="delete_user", methods="POST")
     */
    public function delete_user(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/suspect_user/{id}", name="suspect_user", methods="POST")
     */
    public function suspect_user(User $user)
    {
        $user->setIsSuspected(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }

    /**
     * @Route("/raise_suspicion/{id}", name="raise_suspicion", methods="POST")
     */
    public function raise_suspicion(User $user)
    {
        $user->setIsSuspected(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(
            ['result' => true],
            200,
            ['Access-Control-Allow-Origin' => '*']
        );
    }
}