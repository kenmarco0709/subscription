<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\PurokEntity;
use App\Form\PurokForm;

use App\Service\AuthService;

/**
 * @Route("/purok")
 */
class PurokController extends AbstractController
{
    /**
     * @Route("", name="purok_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Purok'))) return $authService->redirectToHome();
        
       $page_title = ' Purok'; 
       return $this->render('Purok/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/purok/index.js') ]
       );
    }


    /**
     * @Route("/ajax_list", name="purok_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(PurokEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
     *      name = "purok_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Purok New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $purok = $em->getRepository(PurokEntity::class)->find(base64_decode($id));
        if(!$purok) {
            $purok = new PurokEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(PurokForm::class, $purok, $formOptions);

        if($request->getMethod() === 'POST') {

            $purok_form = $request->get($form->getName());
            $result = $this->processForm($purok_form, $purok, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('purok_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('purok_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('purok_form'), 302);
                }
            } else {
                $form->submit($purok_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Purok';

        return $this->render('Purok/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id
        ));
    }

    private function processForm($purok_form, $purok ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(PurokEntity::class)->validate($purok_form);

        if(!count($errors)) {

            switch($purok_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($purok);
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

                        $em->persist($purok);

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

                        $purok->setIsDeleted(true);
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
     * @Route("/autocomplete", name="purok_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'puroks',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(PurokEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }    
}
