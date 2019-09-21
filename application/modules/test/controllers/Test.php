<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/7/24
 * Time: 21:49
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends MX_Controller
{
    public function __construct()
    {
        log_message('debug','Test Controller Initialized');
        parent::__construct();
    }

    public function index()
    {
        var_dump(__LINE__);exit();
    }

    public function message()
    {
        var_dump('message');exit();
    }

    public function lang()
    {
        /*$LANG = & load_class('Lang', 'core');
        $LANG->load('test', 'zh_cn');
        var_dump($LANG->line('test.successful'));*/

        // 辅助函数 language_helper.php
        $this->load->helper('language');
        $this->lang->load('test', 'zh_cn');
        var_dump(lang('test.successful'));
    }

    /**
     * 加载顺序
     * INFO - 2019-09-09 14:07:08 --> Config Class Initialized
     * INFO - 2019-09-09 14:07:08 --> Hooks Class Initialized
     * DEBUG - 2019-09-09 14:07:08 --> UTF-8 Support Enabled
     * INFO - 2019-09-09 14:07:08 --> Utf8 Class Initialized
     * INFO - 2019-09-09 14:07:08 --> URI Class Initialized
     * DEBUG - 2019-09-09 14:07:08 --> MY_Router Initialized
     * INFO - 2019-09-09 14:07:08 --> Router Class Initialized
     * INFO - 2019-09-09 14:07:08 --> Output Class Initialized
     * INFO - 2019-09-09 14:07:08 --> Security Class Initialized
     * DEBUG - 2019-09-09 14:07:08 --> Global POST, GET and COOKIE data sanitized
     * INFO - 2019-09-09 14:07:08 --> CSRF cookie sent
     * INFO - 2019-09-09 14:07:08 --> Input Class Initialized
     * INFO - 2019-09-09 14:07:08 --> Language Class Initialized
     * INFO - 2019-09-09 14:07:08 --> Language Class Initialized
     * INFO - 2019-09-09 14:07:08 --> Config Class Initialized
     * DEBUG - 2019-09-09 14:07:08 -->  MY_Loader Initialized
     * INFO - 2019-09-09 14:07:08 --> Loader Class Initialized
     * DEBUG - 2019-09-09 14:07:08 --> Config file loaded: G:\wamp\www\CodeIgniter_hmvc\application\config/codeigniter.php
     * INFO - 2019-09-09 14:07:08 --> Helper loaded: array_helper
     * INFO - 2019-09-09 14:07:08 --> Helper loaded: language_helper
     * INFO - 2019-09-09 14:07:08 --> Controller Class Initialized
     * DEBUG - 2019-09-10 13:06:08 --> Test Controller Initialized
     * DEBUG - 2019-09-09 14:07:08 --> Test MX_Controller Initialized
     * INFO - 2019-09-09 14:07:08 --> Database Driver Class Initialized
     * INFO - 2019-09-09 14:07:08 --> Model "Test_model" initialized
     * INFO - 2019-09-09 14:07:08 --> Final output sent to browser
     * DEBUG - 2019-09-09 14:07:08 --> Total execution time: 0.0333
     */
    public function database()
    {
        $this->load->model('test_model');
        $list = $this->test_model->get_list_by_query_sql();
        var_dump($list);
    }

    public function add()
    {
        $params = $this->input->get();
        $this->load->library('form_validation');
        $this->form_validation->set_data($params);// 使用数组设置验证规则，如果使用set_data()的话，须在定义任何验证规则之前调用
        //$this->form_validation->set_rules('score', '分数', 'required',['required' => 'You must provide a %s.']);// 设置验证规则
        if ($this->form_validation->run() === false) {
            echo validation_errors();// 返回验证器返回的所有错误信息。如果没有错误信息，它将返回空字符串。
        } else {
            $this->load->model('test_model');
            $this->test_model->set_by_query_sql($this->input->get());
        }
    }

    public function update()
    {
        $params = $this->input->get();
        $this->load->library('form_validation');
        $this->form_validation->set_data($params);

        if ($this->form_validation->run() === false) {
            echo validation_errors();
        } else {
            $params = $this->form_validation->get_validation_params($params);
            $this->load->model('test_model');
            $this->test_model->update_by_id($params);
        }
    }

    public function delete()
    {
        $this->load->model('test_model');
        $this->test_model->delete_by_id($this->input->get());
    }

    public function post()
    {
        $input = $this->input->post();
        var_dump($input);
    }
}