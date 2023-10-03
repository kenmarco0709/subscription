<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\DBAL\Connection;

use App\Entity\ClientAccountBilling;

Class TransactionService {

    private $em;
    private $container;
    private $conn; 
    public function __construct(EntityManagerInterface $em, ContainerInterface $container, Connection $connection) {

        $this->em = $em;
        $this->container = $container;
        $this->conn  = $connection;
    }

    public function processTransaction($clientAccount){


        $query = 'SELECT id, billed_amount, client_account_id  FROM client_account_billing WHERE client_account_id =' .$clientAccount->getId() . ' AND is_deleted = 0 ORDER BY billing_date asc';
        $query = $this->conn->prepare($query);
        $r= $query->executeQuery();
        $billings = $r->fetchAllAssociative();

        $totalPayment = floatval($this->clientAccountPaymentTotal($clientAccount));
        $remainingFromLastBilled = $clientAccount->getOldBalance() ? floatval($clientAccount->getOldBalance()) : 0;
        $finalBalance = $clientAccount->getFinalBalance() ? floatval($clientAccount->getFinalBalance()) : 0;
        
        $totalPayment-= $remainingFromLastBilled;
        if($totalPayment >= $remainingFromLastBilled){

            $remainingFromLastBilled = 0;
            $finalBalance = 0;
        } else {

            $remainingFromLastBilled = abs($totalPayment);
            $finalBalance = abs($totalPayment);
        }

        if(count($billings)){

            foreach ($billings as $k =>  $billing) {
                
                $totalPayment -= $billing['billed_amount'];  
                if($totalPayment >= 0 ){
                    $status = 'Paid';
                        $remainingFromLastBilled = 0;
                        $finalBalance = 0;

                } else {
                    $status = 'Pending Payment';
                    if($k !== array_key_last($billings)){
                        $remainingFromLastBilled = abs($totalPayment);
                    }

                    $finalBalance = abs($totalPayment);

                } 
                $sql = 'Update client_account_billing SET status = "'.$status.'" WHERE id=' . $billing['id'];
                $sql = $this->conn->prepare($sql);
                $sql->executeQuery();
            }

            $sql = 'Update client_account SET remaining_balance = '.$remainingFromLastBilled.', final_balance = '.$finalBalance.' WHERE id=' .  $clientAccount->getId();
            $sql = $this->conn->prepare($sql);
            $sql->executeQuery();          
        }
    }

    private function clientAccountPaymentTotal($clientAccount)
    {

        $query = 'SELECT SUM(amount) AS totalAmt FROM client_account_payment WHERE client_account_id =' .$clientAccount->getId() . ' AND is_deleted != "1" GROUP BY client_account_id'  ;
        $query = $this->conn->prepare($query);
        $r= $query->executeQuery();
        $billings = $r->fetchAllAssociative();

        return $billings ? $billings[0]['totalAmt'] : 0;
    }
}