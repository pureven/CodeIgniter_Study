<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/7/24
 * Time: 21:43
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Test_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_list_by_query_sql()
    {

        //$query = $this->db->query('select * from test;');
        //var_dump($query);exit();

        $query = $this->db->get('test');
        return $query->result_array();
    }


}