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
            'test_server' => ['function' => 'Xmlrpc_server.test'],
        ];

        $config['object'] = $this;

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