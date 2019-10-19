<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/26
 * Time: 22:12
 */
defined('BASEPATH') or exit("No direct script access allowed");

// include_once(APPPATH . 'libraries/REST_Controller.php');

class Blog extends Api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('blog_model');
    }

    public function index_post()
    {
        // $input = $this->input->post();
        $input = $this->post();
        $result = $this->blog_model->add_blog($input); // 成功返回true

        $this->response([
            'ret' => SUCCESSS,
            'msg' => '',
            'data' => [
                'code' => SUCCESSS,
                'message' => 'Blog created successfully.',
            ],
        ], REST_Controller::HTTP_OK);
    }

    public function index_get()
    {
        $page = $this->get('page') ?: 1;
        $limit = $this->get('limit') ?: 10;

        $result = $this->blog_model->get_list($limit, $page);

        $this->response([
            'ret' => SUCCESSS,
            'msg' => '',
            'data' => $result,
        ], REST_Controller::HTTP_OK);
    }

    public function id_get($id)
    {
        $result = $this->blog_model->get_by_id($id);

        $this->response([
            'ret' => SUCCESSS,
            'msg' => '',
            'data' => $result,
        ], REST_Controller::HTTP_OK);
    }

    public function id_put($id)
    {
        $where['blog_id'] = $id;
        $input = $this->put();
        $result = $this->blog_model->update_blog($input, $where); // 成功返回true

        $this->response([
            'ret' => SUCCESSS,
            'msg' => '',
            'data' => [
                'code' => SUCCESSS,
                'message' => 'Blog created successfully.',
            ],
        ], REST_Controller::HTTP_OK);
    }

}