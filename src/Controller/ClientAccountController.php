<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

use App\Service\AuthService;

use App\Entity\BranchEntity;
use App\Entity\BranchVariableEntity;
use App\Entity\ClientAccountEntity;
use App\Entity\ClientAccountBillingEntity;
use App\Entity\PurokEntity;
use App\Form\ClientAccountForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/client_account")
 */
class ClientAccountController extends AbstractController
{

   /**
    * @Route("/details/{id}", name="client_account_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Client Details Account Details'))) return $authService->redirectToHome();
      

       $clientAccount  = $this->getDoctrine()->getManager()->getRepository(ClientAccountEntity::class)->find(base64_decode($id)); 
       $page_title = ' Account Details'; 
       return $this->render('ClientAccount/details.html.twig', [ 
          'page_title' => $page_title,
          'clientAccount' => $clientAccount, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/client_account/details.js') 
         ]);
   }

 /**
     * @Route("/ajax_form", name="client_account_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $client = $em->getRepository(ClientAccountEntity::class)->find(base64_decode($formData['id']));
       
       if(!$client) {
          $client = new ClientAccountEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'clientId' => $formData['clientId']);
       $form = $this->createForm(ClientAccountForm::class, $client, $formOptions);
    
       $result['html'] = $this->renderView('ClientAccount/ajax_form.html.twig', [
            'page_title' => 'New Account',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_account_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_account_form');
         
         $em = $this->getDoctrine()->getManager();

         $errors = $em->getRepository(ClientAccountEntity::class)->validate($clientForm);
         if(!count($errors)){
            
            $clientAccount = $em->getRepository(ClientAccountEntity::class)->find($clientForm['id']);
            
            if(!$clientAccount) {
               $clientAccount = new ClientAccountEntity();
            }
     
            $formOptions = array('action' => $clientForm['action'] , 'clientId' => $clientForm['client']);
            $form = $this->createForm(ClientAccountForm::class, $clientAccount, $formOptions);


            switch($clientForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($clientAccount);
                        $em->flush();

                        $clientAccount->setfinalBalance($clientAccount->getOldBalance());

                        $em->flush();
   
                        $result['msg'] = 'Account successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops1 something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        $em->persist($clientAccount);
                        $em->flush();
                        $result['msg'] = 'Account successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops2 something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $clientAccount->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Account successfully deleted.';
      
                     } else {
      
                        $result['error'] = 'Ooops 3something went wrong please try again later.';
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

   /**
    * @Route("/ajax_list", name="client_account_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(ClientAccountEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

   /**
     * @Route("/ajax_details", name="client_account_ajax_details")
     */
    public function ajaxDetails(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $r = $request->query->get('clientAccountId');

       $clientAccount = $em->getRepository(ClientAccountEntity::class)->find(base64_decode($r));
       $lastBilling = $em->getRepository(ClientAccountBillingEntity::class)->findOneBy([ 'isDeleted' => false, 'clientAccount' => $clientAccount->getId()],['id' => 'desc']);

    
       $result['html'] = $this->renderView('ClientAccount/ajax_detail.html.twig', [
            'clientAccount'=> $clientAccount,
            'lastBilling' => $lastBilling
        ]);

       return new JsonResponse($result);
   }

      /**
     * @Route("/print/temporary_receipt/{id}", name = "client_account_print_temporary_receipt")
     */
    public function printTemporaryReceipt(Request $request, AuthService $authService, Pdf $pdf, $id){

      ini_set('memory_limit', '2048M');

      $accountBilling  = $this->getDoctrine()->getManager()->getRepository(ClientAccountBillingEntity::class)->find(base64_decode($id));

      $options = [
          'orientation' => 'portrait',
          'print-media-type' =>  True,
          'zoom' => .7,
          'margin-top'    => 5,
          'margin-right'  => 5,
          'margin-bottom' => 5,
          'margin-left'   => 5,
      ];
      $em = $this->getDoctrine()->getManager();

      $pricePerCubic = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  $accountBilling->getClientAccount()->getConnectionType(). ' - Price Per Cubic', 'branch' => $accountBilling->getClientAccount()->getClient()->getBranch()->getId()));
      $maximumConsumeBeforeMinimum = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Maximum Consume Before Minimum', 'branch'=> $accountBilling->getClientAccount()->getClient()->getBranch()->getId()));
      $minimumBilledAmount = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Minimum Billed Amount',  'branch' => $accountBilling->getClientAccount()->getClient()->getBranch()->getId()));
      $firstBillingWithRemainingBalance = $em->getRepository(ClientAccountBillingEntity::class)->findOneBy([ 'isDeleted' => false, 'clientAccount' => $accountBilling->getClientAccount()->getId(), 'status' => 'Pending Payment'],['id' => 'asc']);

      $newContent = $this->renderView('ClientAccount/print_temporary_receipt.wkpdf.twig', array(
          'accountBilling' => $accountBilling,
          'pricePerCubic' => $pricePerCubic,
          'maximumConsumeBeforeMinimum' =>  $maximumConsumeBeforeMinimum,
          'minimumBilledAmount' =>  $minimumBilledAmount,
          'firstBillingWithRemainingBalance' => $firstBillingWithRemainingBalance

      ));

      $xml = $pdf->getOutputFromHtml($newContent,$options);
      $pdfResponse = array(
          'success' => true,
          'msg' => 'PDF was successfully generated.', 
          'pdfBase64' => base64_encode($xml)
      );
     
      $pdfContent = $pdfResponse['pdfBase64'];
       return new Response(base64_decode($pdfContent), 200, array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition'   => 'attachment; filename="'.  'receipt' .'-' . date('m/d/Y') . '.pdf"'
      ));
  }

   /**
    * @Route("/ajax_for_billing_list", name="client_account_ajax_for_billing_list")
    */
    public function ajax_for_billing_listAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
         $result = $this->getDoctrine()->getManager()->getRepository(ClientAccountEntity::class)->ajax_for_billing_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

   
    /**
    * @Route("/ajax_pending_payment_list", name="client_account_ajax_pending_payment_list")
    */
    public function ajax_pending_listAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
          $result = $this->getDoctrine()->getManager()->getRepository(ClientAccountEntity::class)->ajax_pending_payment_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

      /**
     * @Route("/print/master_list/{purok}", name = "client_account_print_master_list")
     */
    public function printMasterList(Request $request, AuthService $authService, Pdf $pdf, $purok){

      ini_set('memory_limit', '2048M');

      $options = [
          'orientation' => 'portrait',
          'print-media-type' =>  True,
          'zoom' => .7,
          'margin-top'    => 5,
          'margin-right'  => 5,
          'margin-bottom' => 5,
          'margin-left'   => 5,
      ];
      $em = $this->getDoctrine()->getManager();
      $masterLists = $em->getRepository(ClientAccountEntity::class)->master_list($purok, $this->get('session')->get('userData'));
      $purok = $em->getRepository(PurokEntity::class)->find(base64_decode($purok));
     
      $newContent = $this->renderView('ClientAccount/print_master_list.wkpdf.twig', array(
         'masterLists' => $masterLists,
         'user' => $authService->getUser(),
         'purok' => $purok
      ));

      $xml = $pdf->getOutputFromHtml($newContent,$options);
      $pdfResponse = array(
          'success' => true,
          'msg' => 'PDF was successfully generated.', 
          'pdfBase64' => base64_encode($xml)
      );
     
      $pdfContent = $pdfResponse['pdfBase64'];
       return new Response(base64_decode($pdfContent), 200, array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition'   => 'attachment; filename="'.  'master_list' .'-' . date('m/d/Y') . '.pdf"'
      ));
  }
}