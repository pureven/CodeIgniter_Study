<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2020/3/21
 * Time: 20:00
 */
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('rs')) {
    function rs($msg = null, $default = [])
    {
        $rs['code'] = SUCCESS;
        $rs['message'] = $msg;
        $rs = array_merge($rs, $default);
        return $rs;
    }
}