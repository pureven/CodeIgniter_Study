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
        $rs = ['ret' => 200, 'msg' => '', 'data' => [
            'code' => SUCCESSS,
            'message' => 'Successfully updated to the latest version',

        ]];
        $this->load->library('migration');
        if ($this->migration->current() === FALSE) {
            $rs['data'] = [
                'code' => FAILED,
                'message' => $this->migration->error_string(),
            ];
        }
        header('Content-Type:application/json');
        echo json_encode($rs);
    }

}