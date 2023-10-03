<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\UserEntity;

Class AuthService {

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container) {

        $this->em = $em;
        $this->container = $container;
    }

    public function isLoggedIn($requestUri = true) {

        $session = $this->container->get('session');

        if($session->has('userData')) {
            return true;
        } else {
            if($requestUri) {
                $req_uri = $_SERVER['REQUEST_URI'];
                if($req_uri !== $this->container->get('router')->generate('auth_login') &&
                    $req_uri !== $this->container->get('router')->generate('auth_logout') &&
                    strpos($req_uri, 'ajax') === false) $session->set('req_uri', $req_uri);
            }
            return false;
        }
    }

    /**
     * Redirects to login page
     */
    public function redirectToLogin() {

        return new RedirectResponse($this->container->get('router')->generate('auth_login'), 302);
    }

      /**
     * Get user
     */
    public function getUser() {

        $userData = $this->container->get('session')->get('userData');
        return $this->em->getRepository(UserEntity::class)->find($userData['id']);
    }

    // Original PHP code by Chirp Internet: www.chirp.com.au
    public function better_crypt($input, $rounds = 7)
    {
        $salt = "";
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        for($i=0; $i < 22; $i++) {
            $salt .= $salt_chars[array_rand($salt_chars)];
        }
        return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
    }

    /**
     * Get user types
     */
    public function getUserTypes() {

        return array(
            'Admin',
            'Staff',
            'Cashier',
        );
    }

    /**
     * Get sms types
     */
    public function getSmsTypes() {

        return array(
            'Pending - Remaining Balance'
        );
    }



    public function getAccesses() {

        return array(
            array('label' => 'Dashboard', 'description' => 'Dashboard', 'children' => array(
                array('label' => 'Payment', 'description' => 'Dashboard Payment', 'children' => array(
                    array('label' => 'New', 'description' => 'Dashboard Payment New'),
                    array('label' => 'Print Billing', 'description' => 'Dashboard Payment Print Billing'),
                )),
                 
                array('label' => 'Sms', 'description' => 'Dashboard Sms'),
            )),
            array('label' => 'Report', 'description' => 'Report', 'children' => array(
                array('label' => 'Consumption', 'description' => 'Report Consumption'),
                array('label' => 'Income', 'description' => 'Report Income'),
                array('label' => 'Billing', 'description' => 'Report Billing')

            )),
            array('label' => 'Client', 'description' => 'Client', 'children' => array(
                array('label' => 'New', 'description' => 'Client New'),
                array('label' => 'Update', 'description' => 'Client Update'),
                array('label' => 'Delete', 'description' => 'Client Delete'),
                array('label' => 'Details', 'description' => 'Client Details', 'children' => array(
                    array('label' => 'Account', 'description' => 'Client Details Account',  'children' => array(
                        array('label' => 'New', 'description' => 'Client Details Account New'),
                        array('label' => 'Update', 'description' => 'Client Details Account Update'),
                        array('label' => 'Delete', 'description' => 'Client Details Account Delete'),
                        array('label' => 'Details', 'description' => 'Client Details Account Details', 'children' => array(
                            array('label' => 'Billing', 'description' => 'Client Details Account Details Billing', 'children' => array(
                                array('label' => 'New', 'description' => 'Client Details Account Details Billing New'),
                                array('label' => 'Update', 'description' => 'Client Details Account Details Billing Update'),
                                array('label' => 'Delete', 'description' => 'Client Details Account Details Billing Delete'),
                                array('label' => 'Print', 'description' => 'Client Details Account Details Billing Print'),
                            )),
                            array('label' => 'Payment', 'description' => 'Client Details Account Details Payment', 'children' => array(
                                array('label' => 'New', 'description' => 'Client Details Account Details Payment New'),
                                array('label' => 'Update', 'description' => 'Client Details Account Details Payment Update'),
                                array('label' => 'Delete', 'description' => 'Client Details Account Details Payment Delete'),
                                array('label' => 'Print Receipt', 'description' => 'Client Details Account Details Payment Print Receipt'),
                            ))
                        )),

                    )),
                )),
                
            )),
            array('label' => 'Expense', 'description' => 'Expense', 'children' => array(
                array('label' => 'New', 'description' => 'Expense New'),
                array('label' => 'Update', 'description' => 'Expense Update'),
                array('label' => 'Delete', 'description' => 'Expense Delete'),
                
            )),
            // array('label' => 'Company', 'description' => 'Company', 'children' => array(
            //     array('label' => 'Company View', 'description' => 'Company View', 'children' => array(
            //         array('label' => 'User', 'description' => 'Company View User', 'children' => array(
            //             array('label' => 'New', 'description' => 'Company View User New'),
            //             array('label' => 'Update', 'description' => 'Company View User Update'),
            //             array('label' => 'Delete', 'description' => 'Company View User Delete'),
            //         )),
            //         array('label' => 'Branch', 'description' => 'Company View Branch', 'children' => array(
            //             array('label' => 'New', 'description' => 'Company View Branch New'),
            //             array('label' => 'Update', 'description' => 'Company View Branch Update'),
            //             array('label' => 'Delete', 'description' => 'Company View Branch Delete'),
            //         )),
            //         array('label' => 'Access', 'description' => 'Company View Access', 'children' => array(
            //             array('label' => 'Update', 'description' => 'Company View Access'),
            //             array('label' => 'Update', 'description' => 'Company View Access Form'),
            //         )),
            //         array('label' => 'Sms', 'description' => 'Company View Sms', 'children' => array(
            //             array('label' => 'New', 'description' => 'Company View Sms New'),
            //             array('label' => 'Update', 'description' => 'Company View Sms Update'),
            //             array('label' => 'Delete', 'description' => 'Company View Sms Delete'),
            //         )),
            //     ))
            // )),
            array('label' => 'CMS', 'description' => 'CMS', 'children' => array(
    
                array('label' => 'Payment Type', 'description' => 'CMS Payment Type', 'children' => array(
                    array('label' => 'New', 'description' => 'CMS Payment Type New'),
                    array('label' => 'Update', 'description' => 'CMS Payment Type Update'),
                    array('label' => 'Delete', 'description' => 'CMS Payment Type Delete'),
                )),
                array('label' => 'Expense Type', 'description' => 'CMS Expense Type', 'children' => array(
                    array('label' => 'New', 'description' => 'CMS Expense Type New'),
                    array('label' => 'Update', 'description' => 'CMS Expense Type Update'),
                    array('label' => 'Delete', 'description' => 'CMS Expense Type Delete'),
                )),
            )),
            array('label' => 'Settings', 'description' => 'Settings', 'children' => array(
                array('label' => 'Variable', 'description' => 'Settings Branch Variable', 'children' => array(
                    array('label' => 'New', 'description' => 'Settings Branch Variable New'),
                    array('label' => 'Update', 'description' => 'Settings Branch Variable Update'),
                    array('label' => 'Delete', 'description' => 'Settings Branch Variable Delete'),
                )),
            )),
        );
    }

     /**
     * Redirects to home page
     */
    public function redirectToHome() {

        $userData = $this->container->get('session')->get('userData');
        
        return new RedirectResponse($this->container->get('router')->generate('dashboard_index'), 302);
    }

     /**
     * Checks if the user has the ess
     */
    public function isUserHasAccesses($accessDescriptions, $hasErrorMsg=true, $matchCtr=false) {
        $session = $this->container->get('session');
        $userData = $session->get('userData');


        if($userData['type'] === 'Super Admin') {
            return true;
        } else {
            if($matchCtr) {
                $accessCtr = 0;
                foreach($accessDescriptions as $accessDescription) if(in_array($accessDescription, $userData['accesses'])) $accessCtr++;
                $hasAccess = count($accessDescriptions) === $accessCtr;
                if(!$hasAccess) {
                    if($hasErrorMsg) {
                        $session->getFlashBag()->set('error_messages', "You don't have the right to access the page. Please contact the administrator.");
                    }
                    return false;
                } else {
                    return true;
                }
            } else {
                foreach($accessDescriptions as $accessDescription) if(in_array($accessDescription, $userData['accesses'])) return true;
                if($hasErrorMsg) $session->getFlashBag()->set('error_messages', "You don't have the right to access the page. Please contact the administrator.");
                return false;
            }
        }
    }
    
    /**
     * getTimeago
     */
    function timeAgo( $time )
    {
        $time_difference = time() - strtotime($time);

        if( $time_difference < 1 ) { return 'less than 1 second ago'; }
        $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                    30 * 24 * 60 * 60       =>  'month',
                    24 * 60 * 60            =>  'day',
                    60 * 60                 =>  'hour',
                    60                      =>  'minute',
                    1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;

            if( $d >= 1 )
            {
                $t = round( $d );
                return 'about ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
            }
        }
    }
}