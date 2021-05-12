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
     * @Route("/signup", name="app_signup")
     */
    public function signup(ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder, Request $request, UserRepository $userRepository): Response
    {
        // On bloque l'inscription pour le moment :
        //return $this->redirectToRoute('app_login');

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['require_password' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $roles = ['ROLE_USER'];
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

            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash(
                    'error',
                    "Cet adresse e-mail est déjà utilisée"
                );
                return $this->redirectToRoute('app_login');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/signup.html.twig', ['form' =>  $form->createView()]);
    }
}
