<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\CompanyEntity;
use App\Form\CompanyForm;

use App\Entity\UserEntity;
use App\Form\UserForm;
use App\Entity\SmsEntity;
use App\Form\SmsForm;

use App\Entity\BranchEntity;
use App\Form\BranchForm;

use App\Entity\CompanyAccessEntity;
use App\Entity\AuditTrailEntity;

use App\Service\AuthService;

/**
 * @Route("/company")
 */
class CompanyController extends AbstractController
{
    /**
     * @Route("", name="company_index")
     */
    public function index(Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Company'))) return $authService->redirectToHome();
       
       $page_title = ' Company'; 
       return  $this->render('Company/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/company/index.js') ]
       );
    }

     /**
     * @Route("/details/{id}", name="company_details")
     */
    public function details(Request $request, $id, AuthService $authService)
    {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Company View'))) return $authService->redirectToHome();

        $userTypes = array();

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository(CompanyEntity::class)->find(base64_decode($id));

        foreach($authService->getUserTypes() as $userType) {
            $userTypes[] = array('description' => $userType, 'value' => base64_encode($userType));
        }

       $page_title = ' Company Details'; 
       return  $this->render('Company/details.html.twig', [ 
            'userTypes' => $userTypes,
            'company' => $company,
            'page_title' => $page_title, 
            'javascripts' => array(
                '/plugins/datatables/jquery.dataTables.js',
                '/js/company/details.js'
            )]
       );
    }

    /**
     * @Route("/ajax_list", name="company_ajax_list")
     */
    public function ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $hasUpdate = $authService->getUser()->getType() == 'Super Admin' ? true : false;
            $hasView = $authService->isUserHasAccesses(array('Company View')) ? true : false;

            $data = $this->getDoctrine()->getManager()->getRepository(CompanyEntity::class)->ajax_list($get, $this->get('session')->get('userData'));

            foreach($data['results'] as $row) {

                $id = base64_encode($row['id']);
                $action = '';

                if($hasUpdate) {
                    $url = $this->generateUrl('company_form', array(
                        'action' => 'u',
                        'id' => $id
                    ));
                   
                    $action = "<a class='action-button-style btn btn-primary' href='$url'>Update</a>";
                }

                if($hasView){
                   
                    $viewUrl = $this->generateUrl('company_details', array(
                        'id' => $id
                    ));
                    $action .= "<a class='action-button-style btn btn-secondary ml-3' href='$viewUrl' >View</a>";
                }

                $values = array(
                    $row['code'],
                    $row['description'],
                    $action
                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

       /**
     * @Route(
     *      "/form/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u"
     *      },
     *      name = "company_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $company = $em->getRepository(CompanyEntity::class)->find(base64_decode($id));
        if(!$company) {
            $company = new CompanyEntity();
        }

        $formOptions = array('action' => $action);
        $form = $this->createForm(CompanyForm::class, $company, $formOptions);

        if($request->getMethod() === 'POST') {

            $company_form = $request->get($form->getName());
            $result = $this->processForm($company_form, $company, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('company_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('company_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('company_form'), 302);
                }
            } else {
                $form->submit($company_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Company';

        return  $this->render('Company/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id
        ));
    }

    private function processForm($company_form, $company ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(CompanyEntity::class)->validate($company_form);

        if(!count($errors)) {

            switch($company_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        if(isset($_FILES['company_form']) && !empty($_FILES['company_form']['tmp_name']['logo'])) {

                            $baseName = $company->getId() . '-' . time() . '.' . pathinfo($_FILES['company_form']['name']['logo'], PATHINFO_EXTENSION);
                            $uploadFile = $company->getUploadRootDir() . '/' . $baseName;
          
                            if(move_uploaded_file($_FILES['company_form']['tmp_name']['logo'], $uploadFile)) {
                               $company->setLogoDesc($_FILES['company_form']['name']['logo']);
                               $company->setParsedLogoDesc($baseName);
                            }
                        
                        } 

                        $em->persist($company);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                        $result['redirect'] = 'index';

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;

                case 'u': // update

                    $form->handleRequest($request);

                    if ($form->isValid()) {

                         
                        if(isset($_FILES['company_form']) && !empty($_FILES['company_form']['tmp_name']['logo'])) {

                            $baseName = $company->getId() . '-' . time() . '.' . pathinfo($_FILES['company_form']['name']['logo'], PATHINFO_EXTENSION);
                            $uploadFile = $company->getUploadRootDir() . '/' . $baseName;
          
                            if(move_uploaded_file($_FILES['company_form']['tmp_name']['logo'], $uploadFile)) {
                               $company->setLogoDesc($_FILES['company_form']['name']['logo']);
                               $company->setParsedLogoDesc($baseName);
                            }
                        
                        } 
                        
                        $em->persist($company);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');

                        $result['redirect'] = 'index';

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;

                case 'd': // delete
                    $form->handleRequest($request);


                    if ($form->isValid()) {

                        $company->setIsDeleted(true);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Deleted.');

                        $result['redirect'] = 'index';

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;
            }

            $result['success'] = true;

        } else {

            foreach($errors as $error) {
                $this->get('session')->getFlashBag()->add('error_messages', $error);
            }

            $result['redirect'] = 'index';
            $result['success'] = false;
        }

        return $result;
    }

    
    /**
     * @Route(
     *      "/{companyId}/user/form/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u"
     *      },
     *      name = "company_user_form"
     * )
     */
    public function userFormAction($companyId, $action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Company View User New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(UserEntity::class)->find(base64_decode($id));
        if(!$user) {
            $user = new UserEntity();
        }

        $formOptions = array('action' => $action, 'userTypes' => $authService->getUserTypes(), 'companyId' => $companyId);
        $form = $this->createForm(UserForm::class, $user, $formOptions);

        if($request->getMethod() === 'POST') {

            $user_form = $request->get($form->getName());
            $result = $this->processUserForm($user_form, $user, $form, $request, $authService);

            if($result['success']) {
                if($result['redirect'] === 'company view') {
                    
                    return $this->redirect($this->generateUrl('company_details', array( 'id' => $companyId ), 302));
                }
            } else {
                $form->submit($user_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' User';

        return  $this->render('Company/user_form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'companyId' => $companyId,
            'id' => $id,
            'javascripts' => array('/js/company/user_form.js') 
        ));
    }

    private function processUserForm($user_form, $user, $form, Request $request, $authService) {

        $em = $this->getDoctrine()->getManager();
        $errors = $em->getRepository(UserEntity::class)->validate($user_form);

        
        if(!count($errors)) {

            switch($user_form['action']) {

                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {

   
                        if(isset($user_form['prc_exporation_date']) && !empty($user_form['prc_exporation_date'])){
                            $pet->setPrcExpirationDate(new \DateTime($user_form['prc_exporation_date']));
                         }

                        $user->setPassword($authService->better_crypt(md5($user_form['password']['first']), 15));
                        $em->persist($user);
                        $em->flush();
                        $newAT = new AuditTrailEntity();
                        $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                        $newAT->entity = $user;
                        $newAT->setRefTable('user');
                        $newAT->parseInformation('New');
                        $em->persist($newAT);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');
                        $result['id'] = $user->getId();
                        $result['redirect'] = 'company view';

                    }
                    else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;

                case 'u': // update

                    $newAT = new AuditTrailEntity();
                    $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                    $newAT->entity = $user;
                    $newAT->setRefTable('user');
                    $newAT->parseOriginalDetails();

                    $form->handleRequest($request);
               

                    if ($form->isValid()) {

                        if(empty($user_form['password']['first'])){
                            $user->setPassword($user->getPassword());
                        } else {
                            $user->setPassword($authService->better_crypt(md5($user_form['password']['first']), 15));
                        }

                        $em->persist($user);
                        $em->flush();

                        $newAT->parseInformation('Update');
                        $em->persist($newAT);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');
                        $result['redirect'] = 'company view';

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }


               

                    break;

                case 'd': // delete


                    $user->setIsDeleted(true);

                    $newAT = new AuditTrailEntity();
                    $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                    $newAT->entity = $user;
                    $newAT->setRefTable('user');
                    $newAT->parseInformation('Delete');
                    $em->persist($newAT);
                    
                    $em->flush();

             

                    $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Deleted.');
                    $result['redirect'] = 'company view';

                    break;
            }

            $result['success'] = true;

        } else {

            foreach($errors as $error) {
                $this->get('session')->getFlashBag()->add('error_messages', $error);
            }

            $result['success'] = false;
        }

        return $result;
    }

    /**
     * @Route(
     *      "/{companyId}/branch/form/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u"
     *      },
     *      name = "company_branch_form"
     * )
     */
    public function branchFormAction($companyId, $action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Company View Branch New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $branch = $em->getRepository(BranchEntity::class)->find(base64_decode($id));
        if(!$branch) {
            $branch = new BranchEntity();
        }

        $formOptions = array('action' => $action , 'companyId' => $companyId);
        $form = $this->createForm(BranchForm::class, $branch, $formOptions);

        if($request->getMethod() === 'POST') {

            $branch_form = $request->get($form->getName());
            $result = $this->processBranchForm($branch_form, $branch, $form, $request, $authService);

            if($result['success']) {
                if($result['redirect'] === 'company view') {
                    
                    return $this->redirect($this->generateUrl('company_details', array( 'id' => $companyId ), 302));
                }
            } else {
                $form->submit($branch_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Branch';

        return  $this->render('Company/branch_form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'companyId' => $companyId,
            'id' => $id
        ));
    }

    private function processBranchForm($branch_form, $branch, $form, Request $request, $authService) {

        $em = $this->getDoctrine()->getManager();
        $errors = $em->getRepository(BranchEntity::class)->validate($branch_form);

        
        if(!count($errors)) {

            switch($branch_form['action']) {

                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {

                        $em->persist($branch);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');
                        $result['id'] = $branch->getId();
                        $result['redirect'] = 'company view';

                    }
                    else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;

                case 'u': // update


                   
                    $form->handleRequest($request);

                    if ($form->isValid()) {

                        $em->persist($branch);

                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');
                        $result['redirect'] = 'company view';

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }
                    break;

                case 'd': // delete


                    $branch->setIsDeleted(true);
                    $em->flush();

                    $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Deleted.');
                    $result['redirect'] = 'company view';

                    break;
            }

            $result['success'] = true;

        } else {

            foreach($errors as $error) {
                $this->get('session')->getFlashBag()->add('error_messages', $error);
            }

            $result['success'] = false;
        }

        return $result;
    }

    /**
     * @Route("/{companyId}/access_form/{userType}",name = "company_access_form")
     */
    public function accessFormAction($companyId , $userType, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Company View Access Form'))) return $authService->redirectToHome();

        
        $em = $this->getDoctrine()->getManager();

        $userTypeDecoded = base64_decode($userType);
        $userAccessDescriptions = array();
        $em = $this->getDoctrine()->getManager();

        $userAccesses = $em->getRepository(CompanyAccessEntity::class)->findBy(array('type' => $userTypeDecoded, 'company' => base64_decode($companyId)));

        foreach($userAccesses as $userAccess) {
            $userAccessDescriptions[] = $userAccess->getDescription();
        }

        $accesses = $authService->getAccesses();
        $title = 'User Access';

        return  $this->render('Company/access_form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'userType' => $userType,
            'userAccessDescriptions' => $userAccessDescriptions,
            'accesses' => $accesses,
            'companyId' => $companyId
        ));
    }

       /**
     * @Route("/access_form_process", name="company_access_form_process")
     */
    public function access_form_processAction(Request $request, AuthService $authService) {

    if(!$authService->isLoggedIn()) return $authService->redirectToLogin();

    set_time_limit(0);

    $userType = $request->get('userType');
    $company = $request->get('company');

    $userTypeDecoded = base64_decode($userType);
    $accesses = $request->get('accesses');

    $em = $this->getDoctrine()->getManager();
    $recordCtr = 0;
    $essDescriptions = array();
    $companyAccesses = $em->getRepository(CompanyAccessEntity::class)->findBy(array('type' => $userTypeDecoded, 'company' => base64_decode($company)));

    foreach($accesses as $ess) {

        if(isSet($ess['isChecked'])) {

            $essDescriptions[] = $ess['description'];

            $essExist = false;
            foreach($companyAccesses as $companyAccess) {
                if($companyAccess->getDescription() === $ess['description']) {
                    $essExist = true;
                    break;
                }
            }

            if(!$essExist) {

                $newCompanyAccess = new CompanyAccessEntity();
                $newCompanyAccess->setType($userTypeDecoded);
                $newCompanyAccess->setDescription($ess['description']);
                $newCompanyAccess->setCompany($em->getReference(CompanyEntity::class, base64_decode($company)));
                $em->persist($newCompanyAccess);

                $recordCtr++;
                if($recordCtr % 50 === 0) $em->flush();
            }
        }
    }

    if($recordCtr > 0 && $recordCtr % 50 > 0) $em->flush();

    $recordCtr = 0;
    foreach($companyAccesses as $companyAccess) {

        if(!in_array($companyAccess->getDescription(), $essDescriptions)) {

            $em->remove($companyAccess);

            $recordCtr++;
            if($recordCtr % 50 === 0) $em->flush();
        }
    }

    if($recordCtr > 0 && $recordCtr % 50 > 0) $em->flush();

    $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

    return $this->redirect($this->generateUrl('company_access_form', array(
        'userType' => $userType,
        'companyId' => $company
    )), 302);
}

    /**
     * @Route(
     *      "/{companyId}/sms/form/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u"
     *      },
     *      name = "company_sms_form"
     * )
     */
    public function smsFormAction($companyId, $action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Company View Sms New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $sms = $em->getRepository(SmsEntity::class)->find(base64_decode($id));
        if(!$sms) {
            $sms = new SmsEntity();
        }

        $formOptions = array('action' => $action, 'smsTypes' => $authService->getSmsTypes(), 'companyId' => $companyId);
        $form = $this->createForm(SmsForm::class, $sms, $formOptions);

        if($request->getMethod() === 'POST') {

            $sms_form = $request->get($form->getName());
            $result = $this->processSmsForm($sms_form, $sms, $form, $request, $authService);

            if($result['success']) {
                if($result['redirect'] === 'company view') {
                    
                    return $this->redirect($this->generateUrl('company_details', array( 'id' => $companyId ), 302));
                }
            } else {
                $form->submit($sms_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Sms';

        return  $this->render('Company/sms_form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'companyId' => $companyId,
            'id' => $id
        ));
    }

    private function processSmsForm($sms_form, $sms, $form, Request $request, $authService) {

        $em = $this->getDoctrine()->getManager();
        $errors = $em->getRepository(SmsEntity::class)->validate($sms_form);

        
        if(!count($errors)) {

            switch($sms_form['action']) {

                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {

                        $em->persist($sms);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');
                        $result['id'] = $sms->getId();
                        $result['redirect'] = 'company view';

                    }
                    else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;

                case 'u': // update

              

                    $form->handleRequest($request);
               

                    if ($form->isValid()) {

                        $em->persist($sms);
                        $em->flush();

           

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');
                        $result['redirect'] = 'company view';

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }


               

                    break;

                case 'd': // delete


                    $sms->setIsDeleted(true);


                    $em->flush();
                    $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Deleted.');
                    $result['redirect'] = 'company view';

                    break;
            }

            $result['success'] = true;

        } else {

            foreach($errors as $error) {
                $this->get('session')->getFlashBag()->add('error_messages', $error);
            }

            $result['success'] = false;
        }

        return $result;
    }

    

}
