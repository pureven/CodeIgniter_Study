<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/27
 * Time: 21:34
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Blog_model extends MY_Model
{
    protected $_table = 'blog';
    protected $primary_key = 'blog_id';

    public function __construct()
    {
        parent::__construct();
    }

    public function add_blog($params)
    {
        return $this->db->insert($this->_table, $params);
    }

    public function update_blog($params, $where)
    {
        return $this->db
            ->where($where)
            ->update($this->_table, $params);
    }

    public function get_list($limit = 10, $page = 1)
    {
        $offset = ($page <= 1) ? 0 : ($page - 1) * $limit;
        $this->limit($limit, $offset);

        $list = $this->as_array()->get_all();
        return [
            'total' => $this->count_all(),
            'list' => $list
        ];
    }

    public function get_by_id($id)
    {
        return $this->get_by('blog_id', $id);
    }

}