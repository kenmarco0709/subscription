<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\BranchVariableEntity;
use App\Form\BranchVariableForm;

use App\Service\AuthService;

/**
 * @Route("/branch_variable")
 */
class BranchVariableController extends AbstractController
{
    /**
     * @Route("", name="branch_variable_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Settings Branch Variable'))) return $authService->redirectToHome();
        
       $page_title = ' Branch Variable'; 
       return $this->render('BranchVariable/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/branch_variable/index.js') ]
       );
    }


    /**
     * @Route("/ajax_list", name="branch_variable_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(BranchVariableEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
     *      name = "branch_variable_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Settings Branch Variable New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $branch_variable = $em->getRepository(BranchVariableEntity::class)->find(base64_decode($id));
        if(!$branch_variable) {
            $branch_variable = new BranchVariableEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(BranchVariableForm::class, $branch_variable, $formOptions);

        if($request->getMethod() === 'POST') {

            $branch_variable_form = $request->get($form->getName());
            $result = $this->processForm($branch_variable_form, $branch_variable, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('branch_variable_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('branch_variable_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('branch_variable_form'), 302);
                }
            } else {
                $form->submit($branch_variable_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Branch Variable';

        return $this->render('BranchVariable/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id
        ));
    }

    private function processForm($branch_variable_form, $branch_variable ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(BranchVariableEntity::class)->validate($branch_variable_form);

        if(!count($errors)) {

            switch($branch_variable_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($branch_variable);
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

                        $em->persist($branch_variable);

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

                        $branch_variable->setIsDeleted(true);
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
     * @Route("/autocomplete", name="branch_variable_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'branch_variables',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(BranchVariableEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }    
}
