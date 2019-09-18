<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/18
 * Time: 21:16
 */
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Input extends CI_Input
{
    public function __construct()
    {
        parent::__construct();
    }

    public function post($index = NULL, $xss_clean = NULL)
    {
        $headers = $this->request_headers();
        if ($this->method(true) === 'POST' && in_array($headers['Content-Type'], ['application/json'])) {
            return $this->_fetch_from_array(json_decode($this->raw_input_stream, true), $index, $xss_clean);
        } else {
            return parent::post($index,$xss_clean);
        }
    }

}