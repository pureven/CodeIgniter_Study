<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/28
 * Time: 20:27
 */
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

    }
}