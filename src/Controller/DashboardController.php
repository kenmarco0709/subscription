<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\BranchVariableEntity;
use App\Entity\ClientAccountPaymentEntity;
use App\Entity\ClientAccountEntity;
use App\Entity\ClientAccountBillingEntity;
use App\Entity\PurokEntity;


use App\Service\AuthService;

/**
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("", name="dashboard_index")
     */
    public function index(Request $request, AuthService $authService)
    {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();

       return $this->render('Dashboard/index.html.twig', ['javascripts' => array('/js/dashboard/index.js')] );

    }



    /**
     * @Route("/billing", name="dashboard_billing")
     */
    public function billing(Request $request, AuthService $authService)
    {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Dashboard Billing'))) return $authService->redirectToHome();
        
        $userData = $this->get('session')->get('userData');
        $puroks = $this->getDoctrine()->getManager()->getRepository(PurokEntity::class)->findBy(array('isDeleted' => 0, 'branch' => base64_decode($userData['branchId'])));

        return $this->render('Dashboard/billing.html.twig', [
            'page_title' => ' Account Billing',
            'puroks' => $puroks,
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/dashboard/billing.js')]
        );

    }

    /**
     * @Route("/payment", name="dashboard_payment")
     */
    public function payment(Request $request, AuthService $authService)
    {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Dashboard Payment'))) return $authService->redirectToHome();

        $userData = $this->get('session')->get('userData');
        $puroks = $this->getDoctrine()->getManager()->getRepository(PurokEntity::class)->findBy(array('isDeleted' => 0, 'branch' => base64_decode($userData['branchId'])));

        return $this->render('Dashboard/payment.html.twig', [
            'page_title' => ' Account Billing Payment',
            'puroks' => $puroks,
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/dashboard/payment.js')]
        );

    }

        /**
     * @Route("/print/billing/{purok}", name = "dashboard_payment_print_billing")
     */
    public function printBilling(Request $request, AuthService $authService, Pdf $pdf, $purok){

        ini_set('memory_limit', '2048M');
  
        $options = [
            'page-size' => 'A4',
            'orientation' => 'portrait',
            'print-media-type' =>  True,
            'zoom' => .7,
            'margin-top'    => 2,
            'margin-right'  => 2,
            'margin-bottom' => 2,
            'margin-left'   => 2,
        ];

        $userData = $this->get('session')->get('userData');
        $em = $this->getDoctrine()->getManager();
        $maximumConsumeBeforeMinimum = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Maximum Consume Before Minimum', 'branch'=> base64_decode($userData['branchId'])));
        $minimumBilledAmount = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Minimum Billed Amount',  'branch' => base64_decode($userData['branchId'])));
        $accountBillingWithPendingPayments = $em->getRepository(ClientAccountEntity::class)->account_with_pending_payment($userData, $purok);
        
        $newContent = $this->renderView('Dashboard/billing.wkpdf.twig', array(
            'accountBillingWithPendingPayments' => $accountBillingWithPendingPayments,
            'maximumConsumeBeforeMinimum' =>  $maximumConsumeBeforeMinimum,
            'minimumBilledAmount' =>  $minimumBilledAmount
  
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
     * @Route("/sms", name="dashboard_sms")
     */
    public function sms(Request $request, AuthService $authService)
    {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Dashboard Sms'))) return $authService->redirectToHome();

        return $this->render('Dashboard/sms.html.twig', [
            'page_title' => ' Sms',
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/dashboard/sms.js')]
        );

    }
    
}
