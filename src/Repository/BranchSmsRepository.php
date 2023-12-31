<?php

namespace App\Repository;

use App\Entity\BranchSmsEntity;
use App\Entity\BranchEntity;


/**
 * BranchSmsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BranchSmsRepository extends \Doctrine\ORM\EntityRepository
{
 

    public function ajax_list(array $get, array $userData){

        $columns = array(
            array('CONCAT(c.`first_name`, " ", c.`last_name`)', 'CONCAT(c.`first_name`, " ", c.`last_name`)', 'client'),
            array('c.`contact_no`', 'c.`contact_no`', 'contactNo'),
            array('cm.`description`', 'cm.`description`', 'accountNo'),
            array('bs.`message`', 'bs.`message`', 'message'),
            array('DATE_FORMAT(bs.`send_at`, "%m/%d/%Y %H:%i")', 'DATE_FORMAT(bs.`send_at`, "%m/%d/%Y %H:%i")', 'dateSent'),
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `branch_sms` bs";
        $joins = "LEFT JOIN `client_account` cm ON cm.`id` = bs.`client_account_id`";
        $joins .= "LEFT JOIN `client` c ON c.`id` = cm.`client_id`";
        $sqlWhere = "";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        if($userData['type'] != 'Super Admin'){

            $sqlWhere .= " AND bs.`branch_id` = :branchId";
            $stmtParams['branchId'] =  base64_decode($userData['branchId']);
        }

        foreach($columns as $key => $column) {
            $select .= ($key > 0 ? ', ' : ' ') . $column[1] . (isset($column[2]) ? ' AS ' . $column[2] : '');
        }

        /*
         * Ordering
         */
        foreach($get['columns'] as $key => $column) {
            if($column['orderable']=='true') {
                if(isSet($get['order'])) {
                    foreach($get['order'] as $order) {
                        if($order['column']==$key) {
                            $orderBy .= (!empty($orderBy) ? ', ' : 'ORDER BY ') . $columns[$key][0] . (!empty($order['dir']) ? ' ' . $order['dir'] : '');
                        }
                    }
                }
            }
        }

        /*
         * Filtering
         */
        if(isset($get['search']) && $get['search']['value'] != ''){
            $aLikes = array();
            foreach($get['columns'] as $key => $column) {
                if($column['searchable']=='true') {
                    $aLikes[] = $columns[$key][0] . ' LIKE :searchValue';
                }
            }
            foreach($asColumns as $asColumn) {
                $aLikes[] = $asColumn . ' LIKE :searchValue';
            }
            if(count($aLikes)) {
                $sqlWhere .= (!empty($sqlWhere) ? ' AND ' : 'WHERE ') . '(' . implode(' OR ', $aLikes) . ')';
                $stmtParams['searchValue'] = "%" . $get['search']['value'] . "%";
            }
        }

        /* Set Limit and Length */
        if(isset( $get['start'] ) && $get['length'] != '-1'){
            $limit = 'LIMIT ' . (int)$get['start'] . ',' . (int)$get['length'];
        }

        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result_count = $res->fetchAllAssociative();




        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy $limit";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        /* Data Count */
        $recordsTotal = count($result_count);


              /*
         * Output
         */
        $output = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => array()
        );

        $url = $get['url'];


        foreach($result as $row) {

            $values = array(
                $row['client'],
                $row['contactNo'],
                $row['accountNo'],
                $row['message'],
                $row['dateSent']
            );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }

    public function autocomplete_suggestions($q, array $userData) {
       
        $stmtParams = array();

        $qs = $q['query'];

        $where = ' WHERE b.`description` LIKE :description';
        $stmtParams['description'] =  "%$qs%"; 

        if($userData['type'] != 'Super Admin' ){

            $where.= ' AND b.`branch_id` = :branchId';
            $stmtParams['branchId'] = base64_decode($userData['branchId']);
        }
      
        $sql = "
            SELECT
                 b.`id`,
                 b.`description` AS data,
                 b.`description` AS value
            FROM `branch_variable` b
            ". $where ."
            AND b.`is_deleted` = 0
            ORDER BY b.`description`
            LIMIT 0,20
        ";


        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
       
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        return $result;
    }

    public function getBranchSmsByClientAccount($clientAccount){
       
        $query = $this->getEntityManager()->getConnection()->prepare("
            SELECT 
                COUNT(bs.`id`) as idCtr
            FROM `branch_sms` bs 
            WHERE bs.`client_account_id` = ".$clientAccount['id']."
            AND  (bs.`send_at` >= DATE(NOW() - INTERVAL 7 DAY) OR bs.`send_at` IS NULL) 
        ");
       
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();
        return $result && $result[0] ? $result[0]['idCtr'] : 0;

    }

}
