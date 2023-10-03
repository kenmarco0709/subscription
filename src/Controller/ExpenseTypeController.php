<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\ExpenseTypeEntity;
use App\Form\ExpenseTypeForm;

use App\Service\AuthService;

/**
 * @Route("/expense_type")
 */
class ExpenseTypeController extends AbstractController
{
    /**
     * @Route("", name="expense_type_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Expense Type'))) return $authService->redirectToHome();
        
       $page_title = ' Expense Type'; 
       return $this->render('ExpenseType/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/expense_type/index.js') ]
       );
    }


    /**
     * @Route("/ajax_list", name="expense_type_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(ExpenseTypeEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
     *      name = "expense_type_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Expense Type New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $expense_type = $em->getRepository(ExpenseTypeEntity::class)->find(base64_decode($id));
        if(!$expense_type) {
            $expense_type = new ExpenseTypeEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(ExpenseTypeForm::class, $expense_type, $formOptions);

        if($request->getMethod() === 'POST') {

            $expense_type_form = $request->get($form->getName());
            $result = $this->processForm($expense_type_form, $expense_type, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('expense_type_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('expense_type_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('expense_type_form'), 302);
                }
            } else {
                $form->submit($expense_type_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Expense Type';

        return $this->render('ExpenseType/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id
        ));
    }

    private function processForm($expense_type_form, $expense_type ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(ExpenseTypeEntity::class)->validate($expense_type_form);

        if(!count($errors)) {

            switch($expense_type_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($expense_type);
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

                        $em->persist($expense_type);

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

                        $expense_type->setIsDeleted(true);
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
     * @Route("/autocomplete", name="expense_type_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'expense_types',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(ExpenseTypeEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }    
}
