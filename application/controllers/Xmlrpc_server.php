<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/14
 * Time: 15:24
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Xmlrpc_server extends MX_Controller
{
    public function index()
    {
        $this->load->library('xmlrpc');
        $this->load->library('xmlrpcs');

        $config['functions'] = [
            'test_server' => ['function' => 'Xmlrpc_server.test'],// test_server 是xmlrpc_client调用的方法，Xmlrpc_server.test是test_server的映射
        ];

        $config['object'] = $this; // 'object' 是个特殊的键，用于传递一个实例对象，当映射的方法无法使用 CodeIgniter 超级对象时，它将是必须的

        $this->xmlrpcs->initialize($config);
        $this->xmlrpcs->serve();
    }

    public function test($request)
    {
        $request and $parameters = $request->output_parameters();
        log_message('debug', var_export($parameters, true));

        $response = json_encode(
            [
                'code' => '0',
                'message' => 'success',
                'data' => $parameters,
            ]
        );

        return $this->xmlrpc->send_response($response);
    }

}