<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Entity\PurokEntity;
use App\Entity\ClientAccountBillingEntity;
use App\Entity\ClientAccountPaymentEntity;
use App\Entity\ExpenseEntity;


use App\Service\AuthService;

/**
 * @Route("/report")
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/consumption", name="report_consumption")
     */
    public function consumption(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Consumption'))) return $authService->redirectToHome();

        $userData = $this->get('session')->get('userData');
        $puroks = $this->getDoctrine()->getManager()->getRepository(PurokEntity::class)->findBy(array('isDeleted' => 0, 'branch' => base64_decode($userData['branchId'])));
        
        $page_title = ' Consumption Report'; 
       return $this->render('Report/consumption.html.twig', [ 
            'puroks' => $puroks,
            'page_title' => $page_title, 
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/report/consumption.js') ]
       );
    }
    
    /**
     * @Route("/income", name="report_income")
     */
    public function income(Request $request, AuthService $authService)
    {
        
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Income'))) return $authService->redirectToHome();

        $userData = $this->get('session')->get('userData');
        $puroks = $this->getDoctrine()->getManager()->getRepository(PurokEntity::class)->findBy(array('isDeleted' => 0, 'branch' => base64_decode($userData['branchId'])));
        
        $page_title = ' Income Report'; 
       return $this->render('Report/income.html.twig', [ 
            'puroks' => $puroks,
            'page_title' => $page_title,
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/report/income.js') 

       ]);
    }

    /**
     * @Route("/billing", name="report_billing")
     */
    public function billing(Request $request, AuthService $authService)
    {
        
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Billing'))) return $authService->redirectToHome();

        $userData = $this->get('session')->get('userData');
        $page_title = ' Billing Report'; 
       return $this->render('Report/billing.html.twig', [ 
            'page_title' => $page_title,
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/report/billing.js') 

       ]);
    }

        /**
     * @Route("/export_billing_csv/{dateFrom}/{dateTo}/{status}", name = "report_export_status_csv")
     */
    public function exportBillingCsv(Request $request, AuthService $authService, $dateFrom, $dateTo, $status ){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $userData = $this->get('session')->get('userData');

        $billings = $this->getDoctrine()->getManager()->getRepository(ClientAccountBillingEntity::class)->billing_report($dateFrom,$dateTo, $status, $this->get('session')->get('userData'));

        $columnRange = range('A', 'Z');
        $cellsData = array(
            array('cell' => 'A1', 'data' => 'Start Date: ' .($dateFrom != 'null' ? $dateFrom : '')),
            array('cell' => 'A2', 'data' => 'End Date: ' .($dateTo != 'null' ? $dateTo : '')),
            array('cell' => 'A3', 'data' => 'Status: ' . $status ),

        );

        $row = 5;
        $cellsData[] = array('cell' => 'A'.$row, 'data' => 'Billing');
        $row++; 

        $cellsData[] = array('cell' => 'A'.$row, 'data' => 'Client');
        $cellsData[] = array('cell' => 'B'.$row, 'data' => 'Account Name');
        $cellsData[] = array('cell' => 'C'.$row, 'data' => 'Billing Date');
        $cellsData[] = array('cell' => 'D'.$row, 'data' => 'Billing Amount');
        $cellsData[] = array('cell' => 'E'.$row, 'data' => 'Remarks');
        $cellsData[] = array('cell' => 'F'.$row, 'data' => 'Status');

        $totalPayment = 0;
        foreach ($billings as $billing) {
            $row++;
            $cellsData[] = array('cell' => 'A'.$row, 'data' => $billing['fullName']);
            $cellsData[] = array('cell' => 'B'.$row, 'data' => $billing['accountName']);
            $cellsData[] = array('cell' => 'C'.$row, 'data' => $billing['billingDate']);
            $cellsData[] = array('cell' => 'D'.$row, 'data' => $billing['billingAmount']);
            $cellsData[] = array('cell' => 'E'.$row, 'data' => $billing['remarks']);
            $cellsData[] = array('cell' => 'F'.$row, 'data' => $billing['status']);
        }
        $row++;

    
        $page_title = 'billing-report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

    /**
     * @Route("/consumption_ajax_list", name="report_consumption_ajax_list")
     */
    public function consumption_ajax_list(Request $request, AuthService $authService) {

     
        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {
            $result = $this->getDoctrine()->getManager()->getRepository(ClientAccountBillingEntity::class)->consumption_ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/export_income_csv/{dateFrom}/{dateTo}/{purok}", name = "report_export_income_csv")
     */
    public function exportIncomeCsv(Request $request, AuthService $authService, $dateFrom, $dateTo, $purok ){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $userData = $this->get('session')->get('userData');

        $payments = $this->getDoctrine()->getManager()->getRepository(ClientAccountPaymentEntity::class)->income_report($dateFrom,$dateTo, $purok, $this->get('session')->get('userData'));
        $expenses = $this->getDoctrine()->getManager()->getRepository(ExpenseEntity::class)->income_report($dateFrom,$dateTo, $purok, $this->get('session')->get('userData'));

        $columnRange = range('A', 'Z');
        $cellsData = array(
            array('cell' => 'A1', 'data' => 'Start Date: ' .($dateFrom != 'null' ? $dateFrom : '')),
            array('cell' => 'A2', 'data' => 'End Date: ' .($dateTo != 'null' ? $dateTo : '')),
        );

        $row = 4;
        $cellsData[] = array('cell' => 'A'.$row, 'data' => 'Collections');
        $row++; 

        $cellsData[] = array('cell' => 'A'.$row, 'data' => 'Client');
        $cellsData[] = array('cell' => 'B'.$row, 'data' => 'Client Account');
        $cellsData[] = array('cell' => 'C'.$row, 'data' => 'Payment Type');
        $cellsData[] = array('cell' => 'D'.$row, 'data' => 'Reference No.');
        $cellsData[] = array('cell' => 'E'.$row, 'data' => 'Payment Date');
        $cellsData[] = array('cell' => 'F'.$row, 'data' => 'Amount');

        $totalPayment = 0;
        foreach ($payments as $payment) {
            $row++;
            $totalPayment += $payment['amount'];
            $cellsData[] = array('cell' => 'A'.$row, 'data' => $payment['fullName']);
            $cellsData[] = array('cell' => 'B'.$row, 'data' => $payment['clientAccount']);
            $cellsData[] = array('cell' => 'C'.$row, 'data' => $payment['paymentType']);
            $cellsData[] = array('cell' => 'D'.$row, 'data' => $payment['refNo']);
            $cellsData[] = array('cell' => 'E'.$row, 'data' => $payment['paymentDate']);
            $cellsData[] = array('cell' => 'F'.$row, 'data' => $payment['amount']);
        }
        $row++;

        $cellsData[] = array('cell' => 'E'.$row, 'data' => 'Total: ');
        $cellsData[] = array('cell' => 'F'.$row, 'data' => $totalPayment);

        $row++;
        $row++;
        $cellsData[] = array('cell' => 'A'.$row, 'data' => 'Expenses');
        $row++;
        $cellsData[] = array('cell' => 'A'.$row, 'data' => 'Expense Type');
        $cellsData[] = array('cell' => 'B'.$row, 'data' => 'Description');
        $cellsData[] = array('cell' => 'C'.$row, 'data' => 'Expense Date');
        $cellsData[] = array('cell' => 'D'.$row, 'data' => 'Amount');

        $totalExpense = 0;
        foreach ($expenses as $expense) {
            $row++;
            $totalExpense += $expense['amount'];
            $cellsData[] = array('cell' => 'A'.$row, 'data' => $expense['expenseType']);
            $cellsData[] = array('cell' => 'B'.$row, 'data' => $expense['description']);
            $cellsData[] = array('cell' => 'C'.$row, 'data' => $expense['expenseDate']);
            $cellsData[] = array('cell' => 'D'.$row, 'data' => $expense['amount']);
        }
        $row++;
        $cellsData[] = array('cell' => 'D'.$row, 'data' => 'Total: ');
        $cellsData[] = array('cell' => 'E'.$row, 'data' => $totalExpense);

        $row++;
        $row++;

        $cellsData[] = array('cell' => 'D'.$row, 'data' => 'Total Collection: ');
        $cellsData[] = array('cell' => 'E'.$row, 'data' => $totalPayment);
        $row++;
        $cellsData[] = array('cell' => 'D'.$row, 'data' => 'Total Expenses: ');
        $cellsData[] = array('cell' => 'E'.$row, 'data' => $totalExpense);
        $row++;
        $cellsData[] = array('cell' => 'D'.$row, 'data' => 'Gross Income: ');
        $cellsData[] = array('cell' => 'E'.$row, 'data' => $totalPayment - $totalExpense);
    
        $page_title = 'income-report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

    /**
     * @Route("/export_consumption_csv/{date}/{purok}", name = "report_export_consumption_csv")
     */
    public function exportConsumptionCsv(Request $request, AuthService $authService, $date, $purok ){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $userData = $this->get('session')->get('userData');

        $reports  = $this->getDoctrine()->getManager()->getRepository(ClientAccountBillingEntity::class)->consumptionData($date, $purok, $this->get('session')->get('userData'));
        $puroks = $this->getDoctrine()->getManager()->getRepository(PurokEntity::class)->findBy(array('isDeleted' => 0, 'branch' => base64_decode($userData['branchId'])));

        
        $cellsData = array();

        $startCol = 'b';
        $column = 'b';
        $cellsData[] = array('cell' => "a1" , 'data' => 'Client Account');
        $colsArray = [];
        $billedArray = [];

        foreach($puroks as $k =>  $purok){
            $colsArray[$column] = 0;
            $billedArray[$column] = 0;


            $cellsData[] = array('cell' => $column. "1" , 'data' => $purok->getDescription());
            $column++;
        }

        $columnRange = range($startCol, $column);

        $rowCtr = 1;
        $totalCount = 0;
        foreach($reports as $report) {
            $rowCtr++;

            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $report['account']);
            $column = 'b';
            foreach($puroks as $k =>  $purok){
                if($purok->getDescription() == $report['purok']){
                    $colsArray[$column] += $report['consume'];
                    $billedArray[$column] += $report['billedAmount'];

                    $cellsData[] = array('cell' => $column. $rowCtr, 'data' => $report['consume'] . ' / ' . $report['billedAmount']);
                }
                $column++;
            }
        }

        $rowCtr++;

        $cellsData[] = array('cell' => "A$rowCtr" , 'data' => 'Total Consume');
        $column = 'b';
        foreach($colsArray as $k =>  $col){
            if($k == $column){
                $cellsData[] = array('cell' => $column. $rowCtr, 'data' => $col);
                $column++;
            }
        }

        $rowCtr++;

        $cellsData[] = array('cell' => "A$rowCtr" , 'data' => 'Total Billed Amount');
        $column = 'b';

        foreach($billedArray as $k =>  $col){
            if($k == $column){
                $cellsData[] = array('cell' => $column. $rowCtr, 'data' => $col);
                $column++;
            }
        }
    
        $page_title = 'consume-report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

    private function export_to_excel($columnRange, $cellsData, $page_title, $customStyle=array()) {


        $spreadSheet = new SpreadSheet();
        $activeSheet = $spreadSheet->getActiveSheet(0);

        foreach($cellsData as $cellData) {
            $activeSheet->getCell($cellData['cell'])->setValue($cellData['data']);
        }

        $activeSheet->getColumnDimension('A')->setAutoSize(true);

        $writer = new Xlsx($spreadSheet);

        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $page_title . '.xlsx"');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }
}
