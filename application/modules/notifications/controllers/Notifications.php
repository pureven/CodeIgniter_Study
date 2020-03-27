<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2020/3/23
 * Time: 20:40
 */
defined('BASEPATH') or exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Notifications extends Api
{
    public function __construct()
    {
        parent::__construct();
    }

    public function email_test_get()
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings

            /**
             *  SMTPDebug 输出信息级别
             *  关闭：DEBUG_OFF
             *  客户端信息：DEBUG_CLIENT
             *  服务器信息：DEBUG_SERVER
             * 所有信息：DEBUG_LOWLEVEL
             */
            $mail->SMTPDebug = SMTP::DEBUG_LOWLEVEL;

            // 使用SMTP
            $mail->isSMTP();

            // 发送服务器，比如：smtp.exmail.qq.com
            $mail->Host       = 'smtp.exmail.qq.com';

            // 使用SMTP认证
            $mail->SMTPAuth   = true;

            // 发件人账号/密码
            $mail->Username   = 'your_name@xxx.com';
            $mail->Password   = 'your_password';

            // 加密技术： tls or ssl
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            // SMTP服务器端口： SMTPS => 465  SMTP => 25
            $mail->Port       = 465;

            // 发件人账号及昵称
            $mail->SetFrom('发件人地址', '发件人名称');
            // 收件人账号及昵称
            $mail->AddAddress('收件人地址', "收件人名称");     // Add a recipient

            $mail->isHTML(true); // true: 'text/html'  false: 'text/plain'
            $mail->Subject = '主题';
            $mail->Body    = '正文';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}