<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/7/15
 * Time: 21:01
 */

class Welcome_modules extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->output->cache(1); // 开启缓存， 1 代表 1分钟更新一次
        $this->load->view('welcome_modules_message');
    }
}