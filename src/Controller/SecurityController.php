<?php

namespace App\Controller;

use App\Entity\User;
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

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
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
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/signup/{type}", name="app_signup", defaults={"type": false})
     */
    public function signup(
        string $type,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer
    ): Response
    {

        if ($type) {
            // On bloque l'inscription pour le moment :
            //return $this->redirectToRoute('app_login');
            $user = new User();

            $form = $this->createForm(UserType::class, $user, ['require_password' => true, 'is_personal' => ($type === 'user')]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $isInstitution = !(bool) intval($request->request->get('is_personal'));

                $roles = ($isInstitution) ? [User::ROLE_INSTITUTION] : [User::ROLE_USER];
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
                    foreach ($errors as $error) {
                        var_dump($error->getMessage());
                    }
                    die('a');
                }

                $entityManager = $this->getDoctrine()->getManager();


                $existingEmailUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
                $existingUsernameUser = $userRepository->findOneBy(['username' => $user->getUsername()]);
                if ($existingUsernameUser || $existingEmailUser) {
                    $this->addFlash(
                        'warning',
                        ($existingEmailUser) ? "Cet adresse e-mail est déjà utilisée" : "Cet identifiant est déjà utilisé"
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
                    ->subject('Accès application SILKC')
                    ->text('Bonjour, merci de valider votre compte en cliquant sur le lien suivant : ' . $code)
                    ->html($html);

                try {
                    $result = $mailer->send($email);
                } catch (\Throwable $exception) {}

                $this->addFlash(
                    'info',
                    "A validation link has been sent to you by e-mail"
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
                        ($errorsMessages && count($errorsMessages) > 0) ? implode("\n", $errorsMessages) : 'An error occured'
                    );
                    return $this->redirectToRoute('app_signup');
                }
            }
        }


        $view = 'security/signup.html.twig';
        if ($type == 'user') {
            $view = 'security/signup_user.html.twig';
        }
        if ($type == 'institution') {
            $view = 'security/signup_institution.html.twig';
        }

        return $this->render($view,
                             [
                                 'form' =>  $type ? $form->createView() : false
                             ]
        );
    }

    /**
     * @Route("/validate_account/{code}", name="validate_account", methods={"GET"})
     */
    public function validate_account($code, Request $request, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['code' => $code]);
        if ($user) {
            $user->setIsValidated(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'info',
                "Votre compte est maintenant validé, merci."
            );
        } else {
            $this->addFlash(
                'warning',
                "Le code de validation est invalide."
            );
        }

        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/forgot_password/", name="forgot_password")
     */
    public function forgot_password(Request $request, UserRepository $userRepository, MailerInterface $mailer)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash(
                    'info',
                    "Unknown email."
                );

                return $this->redirectToRoute('app_login');
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
                ->subject('Change password')
                ->text('Hello, if you have requested to change your password, please click on the following link : ' . $link)
                ->html($html);

            try {
                $result = $mailer->send($email);
            } catch (\Throwable $exception) {}

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'info',
                "A validation link has been sent to you by e-mail"
            );
        }

        return $this->render('security/forgot_password.html.twig');
    }

    /**
     * @Route("/new_password/", name="new_password")
     */
    public function new_password(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
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
                    "Unknown user."
                );

                return $this->redirectToRoute('app_login');
            }
            $lastHour = new \DateTime("now");
            $lastHour->sub(new \DateInterval('PT1H0S'));
            if ($user->getCodeCreatedAt() === null || $lastHour > $user->getCodeCreatedAt()) {
                $this->addFlash(
                    'info',
                    "Code expired."
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
                    "Unknown user."
                );

                return $this->redirectToRoute('app_login');
            }

            $password = $request->request->get('password');
            $confirm = $request->request->get('password');
            if ($password !== $confirm) {
                $this->addFlash(
                    'info',
                    "Non-identical passwords."
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
                "Your password is changed."
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/new_password.html.twig');
    }
}
