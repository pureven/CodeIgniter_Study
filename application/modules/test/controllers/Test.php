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

    public function database()
    {
        $this->load->model('test_model');
        $this->test_model->get_list_by_query_sql();
    }
}