<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\BranchEntity;
use App\Form\BranchForm;


use App\Service\AuthService;

/**
 * @Route("/admin/branch")
 */
class BranchController extends AbstractController
{

    /**
     * @Route("/ajax_list", name="branch_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(BranchEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }



    /**
     * @Route("/autocomplete", name="branch_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'branches',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(BranchEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }




 


   

}
