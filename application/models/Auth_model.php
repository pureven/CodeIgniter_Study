<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/10/17
 * Time: 21:55
 */
defined('BASEPATH') or exit('No direct script access allowed');


class Auth_model extends MY_Model
{
    protected $_table = 'users';

    public function __construct()
    {
        parent::__construct();
    }

    public function login($username, $password)
    {
        $user = $this->get_by('name', $username);
        if (!empty($user)) {
            $login_rs = $this->verify_pwd($password, (array)$user);
            return $login_rs;
        }
        return false;
    }

    public function verify_pwd($pwd, $user)
    {
        return password_verify($pwd, $user['password']);
    }

    public function generate_token()
    {
        $rand = $this->security->get_random_bytes(23);
        $pre_token = ($rand === FALSE)
            ? md5(uniqid(mt_rand(), TRUE))
            : bin2hex($rand);

        $this->db->insert('keys', [
            'key' => $pre_token,
            'level' => 0,
            'ignore_limits' => 0,
            'date_created' => time(),
        ]);

        return substr($pre_token, 0, 23);
    }

}