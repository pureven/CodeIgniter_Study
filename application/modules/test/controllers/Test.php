<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/7/24
 * Time: 21:49
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends CI_Controller
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
}