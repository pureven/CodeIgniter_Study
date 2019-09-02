<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/2
 * Time: 20:51
 */
defined('BASEPATH') or exit('Fuck!!!');

// 可作为自定义类的父类
class My_class
{
    public function __get($name)
    {
        // TODO: Implement __get() method.
        return get_instance()->$name;
    }
}