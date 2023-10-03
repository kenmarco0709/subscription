<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\AuditTrailEntity;
use App\Service\AuthService;


/**
 * @Route("/audit_trail")
 */
class AuditTrailController extends AbstractController
{
    /**
     * @Route("", name="audit_trail_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Audit Trail'))) return $authService->redirectToHome();

        $page_title = 'Audit Trail'; 
        return $this->render('AuditTrail/index.html.twig', [ 
                'page_title' => $page_title, 
                'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/audit_trail/index.js')
            ]
        );
    }

    /**
     * @Route("/ajax_list", name="audit_trail_ajax_list")
     */
    public function ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {
            $result = $this->getDoctrine()->getManager()->getRepository(AuditTrailEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }

    
    /**
     * @Route(
     *      "/details/{id}",
     *      name = "audit_trail_detail"
     * )
     */
    public function detailAction($id,Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Audit Trail Details'))) return $authService->redirectToHome();

        $auditTrail = $this->getDoctrine()->getManager()->getRepository(AuditTrailEntity::class)->find(base64_decode($id));
        $auditTrailDetails = json_decode($auditTrail->getDetails(), true);

        return $this->render('AuditTrail/details.html.twig', array(
            'title' => 'Audit Trail Details',
            'page_title' => 'Audit Trail Details',
            'auditTrail' => $auditTrail,
            'auditTrailDetails' => $auditTrailDetails
        ));
    }

}
