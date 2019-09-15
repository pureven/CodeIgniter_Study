<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/15
 * Time: 18:21
 */
defined('BASEPATH') or exit('No direct script access allowed');

// 使用数组来设置验证规则
$config = [
    'test/add' => [
        [
            'field' => 'name',// 表单域名
            'label' => '名称',// 表单域的“人性化”名字，它将被插入到错误信息中
            'rules' => 'required|min_length[5]|max_length[10]',// 为表单域设置的验证规则
            // 级联规则:'required|min_length[5]|max_length[12]|is_unique[users.username]',
            // 预处理数据: trim, htmlspecialchars, 任何只有一个参数的php原生函数都可以被用作一个规则，这里trim没有起作用，尚未找到原因!!!
            'errors' => [// 当此表单域设置自定义的错误信息，如果没有设置该参数，将使用默认的。
                'required' => 'You must provide a %s.',
            ],
        ],
    ],
    'test/update' => [
        [
            'field' => 'name',
            'label' => '名称',
            'rules' => 'trim|required',
            'errors' => [
                'required' => 'You must provide a %s.',
            ],
        ],
        [
            'field' => 'score',
            'label' => '成绩',
            'rules' => 'required|numeric|less_than_equal_to[150]',
        ],
    ],
];