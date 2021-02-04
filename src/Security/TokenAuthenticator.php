<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $passwordEncoder;
    private $_loginError = null; // Erreur rencontrée lors de l'authentification

    public const API_LOGIN_ROUTE = 'api_login';
    public const USER_UNKNOW_ERROR = 'USER_UNKNOW';
    public const INCORRECT_PASSWORD_ERROR = 'INCORRECT_PASSWORD';

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return (
            $request->headers->has('X-AUTH-TOKEN') ||
            (
                self::API_LOGIN_ROUTE === $request->attributes->get('_route') &&
                ($request->isMethod('GET') || $request->isMethod('POST'))
            )
        );
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $credentials = (
            self::API_LOGIN_ROUTE === $request->attributes->get('_route') &&
            $request->query->get('username') &&
            $request->query->get('password')
        ) ?
            [
                'username' => $request->query->get('username'),
                'password' => $request->query->get('password'),
            ]
            :
            $request->headers->get('X-AUTH-TOKEN');

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

        // if a User is returned, checkCredentials() is called
        $result = (is_array($credentials) && array_key_exists('username', $credentials) && !empty($credentials['username'])) ?
            $this->em->getRepository(User::class)
                ->findOneByEmailOrUsername($credentials['username']) :
            $this->em->getRepository(User::class)
                ->findOneBy(['apiToken' => $credentials]);

        if (!$result)
            $this->_loginError = self::USER_UNKNOW_ERROR;

        return $result;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if (is_array($credentials) && array_key_exists('username', $credentials) && !empty($credentials['username'])) {
            $result = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
        } else {
            $expectedToken = base64_encode(sha1($user->getTokenCreatedAt()->format('Y-m-d H:i:s').$user->getPassword(), true));

            $result = hash_equals($expectedToken, $user->getApiToken());
        }

        if (!$result)
            $this->_loginError = self::INCORRECT_PASSWORD_ERROR;

        return $result;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            'error' => $this->_loginError
            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
