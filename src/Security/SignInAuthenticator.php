<?php

namespace App\Security;

use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;

class SignInAuthenticator extends AbstractFormLoginAuthenticator
{
    private $userRepository;
    private $encoder;
    private $router;
    
    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $encoder, RouterInterface $router)
    {
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->router = $router;
    }
    
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'app_sign_in' && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $formData = $request->request->get('user_sign_in_form');
        
        $request->getSession()->set(Security::LAST_USERNAME, $formData['email']);
        
        return [
            'email' => $formData['email'],
            'password' => $formData['plainPassword']
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $this->userRepository->findOneBy(['email' => $credentials['email']]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->encoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('app_homepage'));
    }
    
    protected function getLoginUrl()
    {
        return $this->router->generate('app_sign_in');
    }
}
