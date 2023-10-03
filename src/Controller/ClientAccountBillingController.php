<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Service\AuthService;
use App\Service\TransactionService;


use App\Entity\BranchEntity;

use App\Entity\BranchVariableEntity;
use App\Entity\ClientAccountBillingEntity;
use App\Entity\ClientAccountEntity;

use App\Form\ClientAccountBillingForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/client_account_billing")
 */
class ClientAccountBillingController extends AbstractController
{

   /**
    * @Route("/details/{id}", name="client_account_billing_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Client Details Account Details'))) return $authService->redirectToHome();
      

       $clientAccount  = $this->getDoctrine()->getManager()->getRepository(ClientAccountBillingEntity::class)->find(base64_decode($id)); 
       $page_title = ' Account Details'; 
       return $this->render('ClientAccountBilling/details.html.twig', [ 
          'page_title' => $page_title,
          'clientAccount' => $clientAccount, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/client/details.js') 
         ]);
   }

 /**
     * @Route("/ajax_form", name="client_account_billing_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();

       $clientAccount = $em->getRepository(ClientAccountEntity::class)->find(base64_decode($formData['clientAccountId']));
       $client = $em->getRepository(ClientAccountBillingEntity::class)->find(base64_decode($formData['id']));
       
       if(!$client) {
          $client = new ClientAccountBillingEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'clientAccountId' => $formData['clientAccountId']);
       $form = $this->createForm(ClientAccountBillingForm::class, $client, $formOptions);
    
       $action = $formData['action'] == 'n' ? 'New' : 'Update';
       $result['html'] = $this->renderView('ClientAccountBilling/ajax_form.html.twig', [
            'page_title' => $action .' Account Billing',
            'clientAccount' => $clientAccount,
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_account_billing_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService, TransactionService $transactionService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_account_billing_form');
         
         $em = $this->getDoctrine()->getManager();
         $clientAccount = $em->getRepository(ClientAccountEntity::class)->find(base64_decode($clientForm['clientAccount']));
         $errors = $em->getRepository(ClientAccountBillingEntity::class)->validate($clientForm, $clientAccount);
         
         if(!count($errors)){
            
            $clientAccountBilling = $em->getRepository(ClientAccountBillingEntity::class)->find($clientForm['id']);
            
            if(!$clientAccountBilling) {
               $clientAccountBilling = new ClientAccountBillingEntity();
            }

            $formOptions = array('action' => $clientForm['action'] , 'clientAccountId' => $clientForm['clientAccount']);
            $form = $this->createForm(ClientAccountBillingForm::class, $clientAccountBilling, $formOptions);
          

            switch($clientForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $this->prepareDataForBilling($clientAccountBilling, $em, 'n');
                        $clientAccountBilling->setStatus('Pending Payment');
                        $em->persist($clientAccountBilling);
                        $em->flush();


                        $transactionService->processTransaction($clientAccount);
   
                        $result['msg'] = 'Account Billing successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update
                     
                     $form->handleRequest($request);

                     if ($form->isValid()) {

                        $this->prepareDataForBilling($clientAccountBilling, $em, 'u');
                        $em->flush();

     

                        $transactionService->processTransaction($clientAccount);
                        $result['msg'] = 'Account Billing successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete

                     $form->handleRequest($request);
                     if ($form->isValid()) {
                       
                        $clientAccountBilling->setIsDeleted(true);
                        $em->flush();
                        
                        $transactionService->processTransaction($clientAccount);
                        $result['msg'] = 'Account Billing successfully deleted.';
      
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
            }
        
         } else {

             $result['success'] = false;
             $result['msg'] = '';
             foreach ($errors as $error) {
                 
                 $result['msg'] .= $error;
             }
         }
     } else {

         $result['error'] = 'Ooops something went wrong please try again later.';
     }
    
       return new JsonResponse($result);
    }

   private function prepareDataForBilling($billing, $em, $action){

      $plan = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' => 'Plan ' . $billing->getClientAccount()->getConnectionType(), 'branch' => $billing->getClientAccount()->getClient()->getBranch()->getId()));  
      $billing->setBilledAmount($plan->getBranchVariableValue());      
      $em->flush();
      
   }



   /**
    * @Route("/ajax_list", name="client_account_billing_ajax_list")
    */
    public function ajax_listAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
          $result = $this->getDoctrine()->getManager()->getRepository(ClientAccountBillingEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }
}