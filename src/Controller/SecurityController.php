<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\UserSignUpFormType;
use App\Form\UserSignInFormType;
use App\Service\UploadHelper;
use App\Controller\BaseController;
use App\Entity\Bet;
use App\Entity\User;
use App\Security\SignInAuthenticator;

class SecurityController extends BaseController
{
    /**
     * @Route("/sign_up", name="app_sign_up")
     */
    public function signUp(
            Request $request,
            UserPasswordEncoderInterface $encoder,
            UploadHelper $uploadHelper,
            GuardAuthenticatorHandler $guard,
            SignInAuthenticator $authenticator
    ) {
        $form = $this->createForm(UserSignUpFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            
            $user->setPassword($encoder->encodePassword($user, $form['plainPassword']->getData()));
            if ($request->files->get('user_sign_up_form')['avatarFile']) {
                $avatarFilename = $uploadHelper->saveUpload($request->files->get('user_sign_up_form')['avatarFile'], $uploadHelper::AVATAR);
                $user->setAvatar($avatarFilename);
            }
            
            $this->em->persist($user);
            $em->flush();
            
            return $guard->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'main');
        }
        
        $this->renderParameters['userForm'] = $form->createView();
        $this->renderParameters['isValid'] = $form->isSubmitted() && $form->isValid() || $request->isMethod('GET');
        
        return $this->render('security/sign_up.html.twig', $this->renderParameters);
    }
    
    /**
     * @Route("/sign_in", name="app_sign_in")
     */
    public function signIn(AuthenticationUtils $authenticationUtils)
    {
        $this->renderParameters['userForm'] = $this->createForm(UserSignInFormType::class)->createView();
        $this->renderParameters['error']    = $authenticationUtils->getLastAuthenticationError();
        $this->renderParameters['username'] = $authenticationUtils->getLastUsername();
        
        return $this->render('security/sign_in.html.twig', $this->renderParameters);
    }
    
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }
    
    /**
     * @Route("/profile", name="app_profile")
     * @IsGranted("ROLE_USER")
     */
    public function profile()
    {
        return $this->render('security/profile/profile.html.twig', $this->renderParameters);
    }
    
    /**
     * @Route("/profile/my_bets", name="app_my_bets")
     * @IsGranted("ROLE_USER")
     */
    public function myBets()
    {
        $this->renderParameters['bets'] = $this->em->getRepository(Bet::class)->getMyBets($this->getUser());
        
        return $this->render('security/profile/my_bets.html.twig', $this->renderParameters);
    }
}
