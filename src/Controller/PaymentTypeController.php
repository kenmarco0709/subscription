<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\PaymentTypeEntity;
use App\Form\PaymentTypeForm;

use App\Service\AuthService;

/**
 * @Route("/payment_type")
 */
class PaymentTypeController extends AbstractController
{
    /**
     * @Route("", name="payment_type_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Payment Type'))) return $authService->redirectToHome();
        
       $page_title = ' Payment Type'; 
       return $this->render('PaymentType/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/payment_type/index.js') ]
       );
    }


    /**
     * @Route("/ajax_list", name="payment_type_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(PaymentTypeEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
     *      name = "payment_type_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Payment Type New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $payment_type = $em->getRepository(PaymentTypeEntity::class)->find(base64_decode($id));
        if(!$payment_type) {
            $payment_type = new PaymentTypeEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(PaymentTypeForm::class, $payment_type, $formOptions);

        if($request->getMethod() === 'POST') {

            $payment_type_form = $request->get($form->getName());
            $result = $this->processForm($payment_type_form, $payment_type, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('payment_type_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('payment_type_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('payment_type_form'), 302);
                }
            } else {
                $form->submit($payment_type_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Payment Type';

        return $this->render('PaymentType/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id
        ));
    }

    private function processForm($payment_type_form, $payment_type ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(PaymentTypeEntity::class)->validate($payment_type_form);

        if(!count($errors)) {

            switch($payment_type_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($payment_type);
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

                        $em->persist($payment_type);

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

                        $payment_type->setIsDeleted(true);
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
     * @Route("/autocomplete", name="payment_type_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'payment_types',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(PaymentTypeEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }    
}
