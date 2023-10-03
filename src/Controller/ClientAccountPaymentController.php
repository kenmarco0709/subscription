<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\ImagickEscposImage;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;


use App\Service\AuthService;
use App\Service\TransactionService;

use Knp\Snappy\Pdf;
use App\Entity\BranchEntity;

use App\Entity\BranchVariableEntity;
use App\Entity\ClientAccountPaymentEntity;
use App\Entity\ClientAccountEntity;
use App\Entity\ClientAccountBillingEntity;
use App\Form\ClientAccountPaymentForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/client_account_payment")
 */
class ClientAccountPaymentController extends AbstractController
{

 
 /**
     * @Route("/ajax_form", name="client_account_payment_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();

       $clientAccount = $em->getRepository(ClientAccountEntity::class)->find(base64_decode($formData['clientAccountId']));
       $client = $em->getRepository(ClientAccountPaymentEntity::class)->find(base64_decode($formData['id']));

       $transactionNo = $em->getRepository(ClientAccountPaymentEntity::class)->getNextTransactionNo($this->get('session')->get('userData'));

       if(!$client) {
          $client = new ClientAccountPaymentEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'clientAccountId' => $formData['clientAccountId'], 'transactionNo' => $transactionNo);
       $form = $this->createForm(ClientAccountPaymentForm::class, $client, $formOptions);
       $lastBilling = $em->getRepository(ClientAccountBillingEntity::class)->findOneBy([ 'isDeleted' => false, 'clientAccount' => $clientAccount->getId()],['id' => 'desc']);

       $result['html'] = $this->renderView('ClientAccountPayment/ajax_form.html.twig', [
            'page_title' => 'New Account Payment',
            'lastBilling' => $lastBilling,
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_account_payment_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService, TransactionService $transactionService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_account_payment_form');
         
         $em = $this->getDoctrine()->getManager();
         $clientAccount = $em->getRepository(ClientAccountEntity::class)->find(base64_decode($clientForm['clientAccount']));

         $errors = $em->getRepository(ClientAccountPaymentEntity::class)->validate($clientForm);
         if(!count($errors)){
            
            $clientAccountPayment = $em->getRepository(ClientAccountPaymentEntity::class)->find($clientForm['id']);
            
            if(!$clientAccountPayment) {
               $clientAccountPayment = new ClientAccountPaymentEntity();
            }

            $formOptions = array('action' => $clientForm['action'] , 'clientAccountId' => $clientForm['clientAccount']);
            $form = $this->createForm(ClientAccountPaymentForm::class, $clientAccountPayment, $formOptions);
          

            switch($clientForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        
                        $em->persist($clientAccountPayment);
                        $em->flush();

                        $this->processFile($_FILES, $clientAccountPayment, $em);
                        $transactionService->processTransaction($clientAccount);

                        $payment = $em->getRepository(ClientAccountPaymentEntity::class)->find($clientAccountPayment->getId());
                        $cashier = $em->getRepository(UserEntity::class)->findOneBy(['username' => $payment->getCreatedBy()]);
                  
                        $result['html'] = $this->renderView('ClientAccountPayment/direct_print_receipt.wkpdf.twig', array(
                            'payment' => $payment,
                            'cashier' => $cashier->getFullName()
                  
                        ));
   
                        $result['msg'] = 'Account Payment successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update
                     
                     $form->handleRequest($request);

                     if ($form->isValid()) {

                        $em->flush();
                        $this->processFile($_FILES, $clientAccountPayment, $em);

                        $transactionService->processTransaction($clientAccount);
                        
                        $result['msg'] = 'Account Payment successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete

                     $form->handleRequest($request);
                     if ($form->isValid()) {
                       
                        $clientAccountPayment->setIsDeleted(true);
                        $em->flush();
                                               
                        $transactionService->processTransaction($clientAccount);
                        $result['msg'] = 'Account Payment successfully deleted.';
      
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

   
   

   /**
    * @Route("/ajax_list", name="client_account_payment_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(ClientAccountPaymentEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

      /**
     * @Route("/print/receipt/{id}", name = "client_account_payment_print_receipt")
     */
    public function printReceipt(Request $request, AuthService $authService, Pdf $pdf, $id){

      ini_set('memory_limit', '2048M');



      $accountBilling  = $this->getDoctrine()->getManager()->getRepository(ClientAccountBillingEntity::class)->find(base64_decode($id));

      $options = [
         'orientation' => 'portrait',
         'enable-javascript' => true,
         'javascript-delay' => 1000,
         'no-stop-slow-scripts' => true,
         'no-background' => false,
         'lowquality' => false,
         'page-width' => '80mm',
         'page-height' => '10cm',
         'margin-left'=>0,
         'margin-right'=>0,
         'margin-top'=>0,
         'margin-bottom'=>0,
         'encoding' => 'utf-8',
         'images' => true,
         'cookie' => array(),
         'dpi' => 300,
         'enable-external-links' => true,
         'enable-internal-links' => true,
          'margin-top'    => 5,
          'margin-bottom' => 5,
      ];
      $em = $this->getDoctrine()->getManager();
      $payment = $em->getRepository(ClientAccountPaymentEntity::class)->find(base64_decode($id));
      $cashier = $em->getRepository(UserEntity::class)->findOneBy(['username' => $payment->getCreatedBy()]);

      $newContent = $this->renderView('ClientAccountPayment/print_receipt.wkpdf.twig', array(
          'payment' => $payment,
          'cashier' => $cashier->getFullName()

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
          'Content-Disposition'   => 'attachment; filename="'.  $payment->getTransactionNo() .'-' . date('m/d/Y') . '.pdf"'
      ));
  }

   private function processFile($files, $payment, $em){


      if(isset($files['client_account_payment_form']) && !empty($files['client_account_payment_form']['tmp_name']['file'])) {
         $baseName = $payment->getId() . '-' . time() . '.' . pathinfo($files['client_account_payment_form']['name']['file'], PATHINFO_EXTENSION);
         $uploadFile = $payment->getUploadRootDir() . '/' . $baseName;

         if(move_uploaded_file($files['client_account_payment_form']['tmp_name']['file'], $uploadFile)) {
            $payment->removeFile();
            $payment->setFileDescription($files['client_account_payment_form']['name']['file']);
            $payment->setParsedFileDescription($baseName);
         }

         $em->flush();
      }  
   }

}