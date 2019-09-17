<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/17
 * Time: 20:50
 */
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation
{
    // 获取trim htmlspecialchars等方法预处理数据后的数据，当然也可以使用set_value()函数来获取
    public function get_validation_params($params)
    {
        foreach ($params as $key=>$value) {
            in_array($key, array_column($this->_field_data, 'field'))
            and $params[$key] = $this->_field_data[$key]['postdata'];
        }
        return $params;
    }
}