<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/21
 * Time: 14:02
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Tools extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function migrate()
    {
        $rs = ['ret' => 200, 'msg' => '', 'data' => rs('Successfully updated to the latest version')];
        $result = $this->__upgrade_db();
        if (!$result) {
            $rs['data'] = rs('Migration failed');
        }
        header('Content-Type:application/json');
        echo json_encode($rs);
    }

    private function __upgrade_db()
    {
        $this->load->library('migrate');
        return $this->migrate->upgrade();
    }

}