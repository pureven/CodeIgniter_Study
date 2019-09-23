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
    protected $_table = 'test';
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_list_by_query_sql()
    {

        // $this->db: object CI_DB_mysqli_driver
        $query = $this->db->query('select * from test;');
        //$query = $this->db->get('test');

        return $query->result_array();
    }

    public function set_by_query_sql($input)
    {
        // name score
        $this->db->insert($this->_table, $input);
        var_dump($this->db->last_query());exit();
    }

    public function update_by_id($input)
    {
        // id name score
        $this->db->update($this->_table, $input, array('id' => $input['id']));
        var_dump($this->db->last_query());exit();
    }

    public function delete_by_id($input)
    {
        // id
        $this->db->delete($this->_table, $input);
        var_dump($this->db->last_query());exit();
    }

}