<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

use App\Entity\CompanyAccessEntity;
use App\Entity\UserEntity;
use App\Form\AuthForm;

use App\Service\AuthService;

/**
 * @Route("/")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("", name="auth_login")
     */
    public function index(Request $request, AuthService $authService)
    {

       if($authService->isLoggedIn()) return $this->redirect($this->generateUrl('dashboard_index'), 302);

       $formOptions = array();
       $form = $this->createForm(AuthForm::class, null, $formOptions);

       if($request->getMethod() == 'POST'){

            $authForm = $request->get($form->getName());
            $errors= [];

            if($authForm['username'] == ''){ $errors[] = 'Username is empty.'; }
            if($authForm['password'] == ''){ $errors[] = 'Password is empty.'; }

            if(!count($errors)){
                $form->handleRequest($request);

                if($form->isValid()){

                    $em = $this->getDoctrine()->getManager();
                    $user = $em->getRepository(UserEntity::class)->findOneBy(array('isDeleted' => NULL, 'username' => $authForm['username']));

                    if(!$user){
                   
                        $this->get('session')->getFlashBag()->set('error_messages', 'Invalid user or user is not actived.');

                    } else {
                       
                        if($user->getPassword() == crypt(md5($authForm['password']), $user->getPassword())){
                            
                            $session = $this->get('session');
                            $session->clear();

                            $accesses = array();

                            if($user->getType() !== 'Super Admin'){
                                $userAccesses = $em->getRepository(CompanyAccessEntity::class)->findBy(array('type' => $user->getType(), 'company' => $user->getCompany()->getId()));
                
                                foreach($userAccesses as $userAccess) {
                                    $accesses[] = $userAccess->getDescription();
                                }
                            }
                         
                            $userData = array(
                                'id' => $user->getId(),
                                'type' => $user->getType(),
                                'companyId' => $user->getCompany() ? $user->getCompany()->getId() : '' ,
                                'branchId' => $user->getBranch() ? $user->getBranch()->getIdEncoded() : '', 
                                'username' => $user->getUsername(),
                                'fullName' => $user->getFullName(),
                                'accesses' => $accesses
                            );

                            $session->set('userData', $userData);
                            $url = $this->generateUrl('dashboard_index');

                            $response = new RedirectResponse($url);
                            $response->headers->setCookie(new Cookie('userId', $user->getIdEncoded(), strtotime('+1 year')));
                            $response->headers->setCookie(new Cookie('username', $user->getUsername(), strtotime('+1 year')));
                            $response->headers->setCookie(new Cookie('remUser', $user->getUsername(), strtotime('+1 year')));
                            $response->headers->setCookie(new Cookie('remPwd', $user->getPassword(), strtotime('+1 year')));
                            $response->send();

                      
                        } else {
                        
                            $this->get('session')->getFlashBag()->set('error_messages', 'Incorrect password.');
                        }
                    }

                } else {

                    $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                }

            } else {

                foreach($errors as $error) {
                    $this->get('session')->getFlashBag()->add('error_messages', $error);
                }   
                
                
            }
       }

       return $this->render('Auth/login.html.twig', [
            'form' => $form->createView()
       ]);
    }

     /**
     * @Route("/logout", name="auth_logout")
     */
    public function logoutAction(AuthService $authService) {

   
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();

        $this->get('session')->clear();

        if(isSet($_COOKIE['userId'])) {
            unset($_COOKIE['userId']);
            setcookie('userId', null, -1, '/');
        }

        if(isSet($_COOKIE['username'])) {
            unset($_COOKIE['username']);
            setcookie('username', null, -1, '/');
        }

        return $authService->redirectToLogin();
    }

    /**
     * @Route("/privacy_policy", name="auth_privacy_policy")
     */
    public function privacy_policy(Request $request, AuthService $authService)
    {

       return $this->render('Auth/privacy_policy.html.twig');

    }
}
