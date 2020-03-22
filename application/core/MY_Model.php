<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/27
 * Time: 21:36
 */

require_once APPPATH . 'core/Base_Model.php';

class MY_Model extends Base_Model
{
    public function __construct()
    {
        $this->load->database();
        parent::__construct();
    }
}