<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\UserEntity;
use App\Form\UserForm;


use App\Service\AuthService;

/**
 * @Route("/admin/user")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/ajax_list", name="user_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(UserEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }



    /**
     * @Route("/autocomplete", name="user_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'users',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(UserEntity::class)->autocompleteSuggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }

    
    /**
     * @Route("/vet_autocomplete", name="user_vet_autocomplete")
     */
    public function vetAutocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'vet_users',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(UserEntity::class)->vet_autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }




 


   

}
