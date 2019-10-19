<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/10/17
 * Time: 21:29
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Auth
 *
 * token_post
 */
class Auth extends Api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
    }

    public function token_post()
    {
        $ret = [
            'ret' => SUCCESSS,
            'msg' => '',
        ];
        // 用户名密码验证
        $username = $this->post('username');
        $password = $this->post('password');
        if ($this->auth_model->login($username, $password)) {
            // 生成token
            $token = $this->auth_model->generate_token();
            @session_regenerate_id(true);

            $ret = [
                'data' => [
                    'code' => SUCCESSS,
                    'message' => '',
                    'token' => $token,
                ],
            ];
        } else {
            $ret = [
                'data' => [
                    'code' => FAILED,
                    'message' => 'Account or password is invalid',
                ],
            ];
        }

        // 返回
        end:
        $this->response($ret, REST_Controller::HTTP_OK);
    }
}