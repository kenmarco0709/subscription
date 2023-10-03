<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\BranchSmsEntity;
use App\Entity\BranchEntity;
use App\Entity\ClientAccountEntity;
use App\Entity\ClientAccountBillingEntity;
use App\Entity\SmsEntity;
use App\Entity\BranchVariableEntity;
use App\Service\TransactionService;


use App\Service\AuthService;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{

     /**
     * @Route("/generate_monthly_billing", name="api_generate_monthly_billing")
     */
    public function generate_monthly_billing(Request $request, AuthService $authService, TransactionService $transactionService)
    {

       $result = [ 'success' => true, 'msg' => ''];
       
       $em = $this->getDoctrine()->getManager();
       $clientAccounts = $em->getRepository(ClientAccountEntity::class)->findAll();
      
        foreach($clientAccounts as $k =>  $clientAccount){
           
            $monthBilling = $em->getRepository(ClientAccountBillingEntity::class)->monthBill($clientAccount->getId());
            $plan = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' => 'Plan ' . $clientAccount->getConnectionType(), 'branch' => $clientAccount->getClient()->getBranch()->getId())); 
       
            if(!count($monthBilling)){
                $newBilling = new ClientAccountBillingEntity();
                $newBilling->setClientAccount($clientAccount);
                $newBilling->setBillingDate(new \Datetime(date('m/d/Y')));
                $newBilling->setDueDate(new \Datetime(date('m/d/Y', strtotime("+5 day"))));
                $newBilling->setBilledAmount($plan->getBranchVariableValue());      
                $newBilling->setStatus('Pending Payment');   
                $em->persist($newBilling);
                $em->flush();

                $transactionService->processTransaction($clientAccount);

            }
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/generate_pending_payment_sms", name="api_generate_pending_payment_sms")
     */
    public function generate_pending_payment_sms(Request $request, AuthService $authService)
    {

       $result = [ 'success' => true, 'msg' => ''];
       
       $em = $this->getDoctrine()->getManager();
       $clientAccounts = $em->getRepository(ClientAccountBillingEntity::class)->get_pending_payment();
      
        foreach($clientAccounts as $clientAccount){

            $sms = $em->getRepository(SmsEntity::class)->findOneBy(array('smsType' => 'Pending - Remaining Balance', 'company' => $clientAccount['companyId']));

            if($sms){
                $smsMesg  = $sms->getMessage();
                $msg = str_replace("[month]",$clientAccount['billingDate'], str_replace("[amount]",$clientAccount['totalBalance'], str_replace("[client]",$clientAccount['fullName'],$smsMesg)));
                
                $a = $em->getRepository(BranchSmsEntity::class)->getBranchSmsByClientAccount($clientAccount);
                
                if($a == 0){
    
                    $branchSmsEntity = new BranchSmsEntity;
                    $branchSmsEntity->setBranch($em->getReference(BranchEntity::class, $clientAccount['branchId']));
                    $branchSmsEntity->setClientAccount($em->getReference(ClientAccountEntity::class, $clientAccount['id']));
                    $branchSmsEntity->setSms($sms);
                    $branchSmsEntity->setMessage($msg);
                    $branchSmsEntity->setStatus($this->validatePhoneNo($clientAccount['contactNo']) ? 'New' : 'Invalid Contact No');
                    $em->persist($branchSmsEntity);
                    $em->flush();
                }
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/get_for_sent_sms", name="api_get_for_sent_sms")
     */
    public function get_for_sent_sms(Request $request, AuthService $authService)
    {

       $result = [ ];
       $em = $this->getDoctrine()->getManager();
       $bs = $em->getRepository(BranchSmsEntity::class)->findOneBy([
            'status' => [
                'New',
                'Sending'
            ]
       ]);
      
       if($bs){
            $result = [ 
                'id' => $bs->getId(),
                'contact_no' => $bs->getClientAccount()->getClient()->getContactNo(),
                'message' => $bs->getMessage(),
                'sent_ctr' => $bs->getSentCtr() ? $bs->getSentCtr() : 0 
            ];
            

        }
       
       return new JsonResponse($result);
    }

    /**
     * @Route("/update_sms/{id}/{status}/{sent_ctr}", name="api_update_sms")
     */
    public function update_sms(Request $request, AuthService $authService, $id, $status, $sent_ctr)
    {

       $result = [ 'success' => true, 'msg' => ''];
       $em = $this->getDoctrine()->getManager();
       $bs = $em->getRepository(BranchSmsEntity::class)->find($id);
      
       if($bs){


            if($status == 'Sending' && $sent_ctr >= 5){
                $status = 'Invalid Contact No';
            }

            $bs->setSentCtr($sent_ctr);
            $bs->setStatus($status);
            $bs->setSendAt(new \DateTime( date('Y-m-d H:i:s') ));
            $em->flush();
       }
       
       return new JsonResponse($result);
    }

    private function validatePhoneNo($phoneNo){
        
        $isVAlid = false;
        if(preg_match("/^(09|\+639)\d{9}$/", $phoneNo)) {
            $isVAlid = true;
        }
        
        return $isVAlid;
        
    }
    
}
