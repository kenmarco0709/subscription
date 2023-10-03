<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Service\AuthService;

use App\Entity\BranchEntity;

use App\Entity\ExpenseEntity;
use App\Form\ExpenseForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/expense")
 */
class ExpenseController extends AbstractController
{
    /**
     * @Route("/ajax_form", name="expense_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $expense = $em->getRepository(ExpenseEntity::class)->find(base64_decode($formData['id']));
       
       if(!$expense) {
          $expense = new ExpenseEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $form = $this->createForm(ExpenseForm::class, $expense, $formOptions);
    
       $result['html'] = $this->renderView('Expense/ajax_form.html.twig', [
            'page_title' => 'New Expense',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="expense_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $expenseForm = $request->request->get('expense_form');
         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(ExpenseEntity::class)->validate($expenseForm);

         if(!count($errors)){
            
            $expense = $em->getRepository(ExpenseEntity::class)->find($expenseForm['id']);
            
            if(!$expense) {
               $expense = new ExpenseEntity();
            }
     
            $formOptions = array('action' => $expenseForm['action'] , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
            $form = $this->createForm(ExpenseForm::class, $expense, $formOptions);


            switch($expenseForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($expense);
                        $em->flush();
   
                        $result['msg'] = 'Expense successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops1 something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        $em->persist($expense);
                        $em->flush();
                        $result['msg'] = 'Expense successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops2 something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $expense->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Expense successfully deleted.';
      
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
    * @Route("", name="expense_index")
    */
   public function index(Request $request, AuthService $authService)
   {
      
      if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
      if(!$authService->isUserHasAccesses(array('Expense'))) return $authService->redirectToHome();
      
      $page_title = ' Expense'; 
      return $this->render('Expense/index.html.twig', [ 
         'page_title' => $page_title, 
         'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/expense/index.js') ]
      );
   }

   
   /**
    * @Route("/ajax_list", name="expense_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(ExpenseEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }


}
