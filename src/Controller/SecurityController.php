<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\InstitutionType;
use App\Form\Type\RecruiterType;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/{_locale<en|fr|pl|it>}/login", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/signup/{type}", name="app_signup", defaults={"type": false}, requirements={"type": "user|institution|recruiter"})
     */
    public function signup(
        string $type,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer,
        TranslatorInterface $translator
    ): Response
    {
        $view = 'security/signup.html.twig';

        if ($type) {
            $user = new User();

            switch($type) {
                case 'recruiter' :
                    $form = $this->createForm(RecruiterType::class, $user, ['require_password' => true]);
                    $roles = [User::ROLE_RECRUITER];
                    $view = 'security/signup_recruiter.html.twig';
                    break;
                case 'institution' :
                    $form = $this->createForm(InstitutionType::class, $user, ['require_password' => true]);
                    $roles = [User::ROLE_INSTITUTION];
                    $view = 'security/signup_institution.html.twig';
                    break;
                case 'user':
                default:
                    $form = $this->createForm(UserType::class, $user, ['require_password' => true]);
                    $roles = [User::ROLE_USER];
                    $view = 'security/signup_user.html.twig';
                    break;
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();

                $createdAt = new \DateTime('now');
                $password = $user->getPassword();
                $password = $passwordEncoder->encodePassword($user, $password);
                $apiToken = base64_encode(sha1($createdAt->format('Y-m-d H:i:s').$password, true));

                $user->setTokenCreatedAt($createdAt);
                $user->setCreatedAt($createdAt);
                $user->setApiToken($apiToken);
                $user->setRoles($roles);
                $user->setPassword($password);
                $errors = $validator->validate($user);

                if (count($errors) > 0) {
                    return new Response((string) $errors, 400);
                }

                $entityManager = $this->getDoctrine()->getManager();

                $existingEmailUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
                $existingUsernameUser = $userRepository->findOneBy(['username' => $user->getUsername()]);
                if ($existingUsernameUser || $existingEmailUser) {
                    $this->addFlash(
                        'warning',
                        ($existingEmailUser) ? $translator->trans("flash.email_already_exists") : $translator->trans("flash.username_already_exists")
                    );
                    return $this->redirectToRoute('app_signup');
                }
                $code = random_int(100000, 999999);
                $user->setCode($code);

                $entityManager->persist($user);
                $entityManager->flush();

                $link = 'https://silkc-platform.org/validate_account/' . $code;
                $html = $this->render('emails/send_code.html.twig', [
                    'validation_link' => $link
                ])->getContent();
                $email = (new Email())
                    ->from('contact@silkc-platform.org')
                    ->to($user->getEmail())
                    ->subject($translator->trans('email.validate_your_account'))
                    ->text($translator->trans('email.to_change_password') . $code)
                    ->html($html);

                try {
                    $result = $mailer->send($email);
                } catch (\Throwable $exception) {}

                $this->addFlash(
                    'info',
                    $translator->trans("flash.validation_link_has_been_sent")
                );

                return $this->redirectToRoute('app_login');
            } elseif ($form->isSubmitted()) {
                $errors = $validator->validate($user);
                if ($errors && count($errors) > 0) {
                    $errorsMessages = [];
                    foreach ($errors as $error) {
                        $errorsMessages[] = $error->getMessage();
                    }
                    $this->addFlash(
                        'warning',
                        ($errorsMessages && count($errorsMessages) > 0) ? implode("\n", $errorsMessages) : $translator->trans('flash.an_error_occured')
                    );
                    return $this->redirectToRoute('app_signup');
                }
            }
        }

        return $this->render(
            $view,
            ['form' =>  $type ? $form->createView() : false]
        );
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/validate_account/{code}", name="app_validate_account", methods={"GET"})
     */
    public function validate_account($code, Request $request, UserRepository $userRepository, TranslatorInterface $translator)
    {
        $user = $userRepository->findOneBy(['code' => $code]);
        if ($user) {
            $user->setIsValidated(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'info',
                $translator->trans("flash.your_account_is_validated")
            );
        } else {
            $this->addFlash(
                'warning',
                $translator->trans("flash.invalid_code")
            );
        }

        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/forgot_password/", name="app_forgot_password")
     */
    public function forgot_password(Request $request, UserRepository $userRepository, MailerInterface $mailer, TranslatorInterface $translator)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash(
                    'info',
                    $translator->trans("flash.unknown_email")
                );

                return $this->redirectToRoute('app_forgot_password');
            }

            $code = random_int(100000, 999999);
            $date = new \DateTime("now");
            $user->setCode($code);
            $user->setCodeCreatedAt($date);

            $link = 'https://silkc-platform.org/new_password?id=' . $user->getId() . '&code=' . $code;
            $html = $this->render('emails/forgot_password.html.twig', [
                'validation_link' => $link
            ])->getContent();
            $email = (new Email())
                ->from('contact@silkc-platform.org')
                ->to($user->getEmail())
                ->subject($translator->trans('email.change_password'))
                ->text($translator->trans('email.change_password_content') . $link)
                ->html($html);

            try {
                $result = $mailer->send($email);
            } catch (\Throwable $exception) {}

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'info',
                $translator->trans("flash.validation_link_has_been_sent")
            );
        }

        return $this->render('security/forgot_password.html.twig');
    }

    /**
     * @Route("/{_locale<en|fr|pl|it>}/new_password/", name="app_new_password")
     */
    public function new_password(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, TranslatorInterface $translator)
    {
        if (
            $request->getMethod() === Request::METHOD_GET &&
            $request->query->get('id') !== null &&
            $request->query->get('code') !== null
        ) {
            $id = $request->query->get('id');
            $code = $request->query->get('code');

            $user = $userRepository->findOneBy(['id' => $id, 'code' => $code]);
            if (!$user) {
                $this->addFlash(
                    'info',
                    $translator->trans("flash.unknown_user")
                );

                return $this->redirectToRoute('app_login');
            }
            $lastHour = new \DateTime("now");
            $lastHour->sub(new \DateInterval('PT1H0S'));
            if ($user->getCodeCreatedAt() === null || $lastHour > $user->getCodeCreatedAt()) {
                $this->addFlash(
                    'info',
                    $translator->trans("flash.expired_code")
                );

                return $this->redirectToRoute('app_login');
            }
        }
        else if ($request->getMethod() === Request::METHOD_POST) {
            $id = $request->query->get('id');
            $code = $request->query->get('code');
            $user = $userRepository->findOneBy(['id' => $id, 'code' => $code]);
            if (!$user) {
                $this->addFlash(
                    'info',
                    $translator->trans("flash.unknown_user")
                );

                return $this->redirectToRoute('app_login');
            }

            $password = $request->request->get('password');
            $confirm = $request->request->get('password');
            if ($password !== $confirm) {
                $this->addFlash(
                    'info',
                    $translator->trans("flash.non_identical_password")
                );

                return $this->redirectToRoute('app_login');
            }

            $createdAt = new \DateTime('now');
            $password = $passwordEncoder->encodePassword($user, $password);
            $apiToken = base64_encode(sha1($createdAt->format('Y-m-d H:i:s').$password, true));

            $user->setTokenCreatedAt($createdAt);
            $user->setCreatedAt($createdAt);
            $user->setPassword($password);
            $user->setApiToken($apiToken);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'info',
                $translator->trans("flash.your_password_is_changed")
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/new_password.html.twig');
    }
}
