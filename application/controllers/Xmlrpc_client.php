<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/14
 * Time: 11:40
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Xmlrpc_client extends MX_Controller
{
    public function index()
    {
        $this->load->helper('url');
        $this->load->library('xmlrpc');

        $this->xmlrpc->server(site_url('xmlrpcs'), 8080);// 设置服务器URL
        $this->xmlrpc->method('test_server');// 要调用的方法

        //$this->xmlrpc->debug = true;

        $request = [
            'param1',
            'param2',
            'param3',
            'param4',
            'param5'
        ];

        $this->xmlrpc->request($request); // 编译请求

        // send_request() 发送完整请求
        if (!$this->xmlrpc->send_request()) {
            echo $this->xmlrpc->display_error();
        } else {
            echo '<pre>';
            print_r($this->xmlrpc->display_response());
            echo '</pre>';
        }
    }

}