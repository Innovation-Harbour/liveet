<?php

namespace LAMATA_EPURSE\Domain;

use Rashtell\Domain\Mailer;

class MailHandler
{
    public $from = 'info@LAMATA_EPURSE.com';
    public $fromName = 'LAMATA_EPURSE';
    private $template = '';
    private  $userType = '';
    private $to = '';
    private $params = '';


    const TEMPLATE_CONFIRM_EMAIL = 1;

    const USER_TYPE_ADMIN = 0;
    const USER_TYPE_ORGANIZATION = 1;
    const USER_TYPE_AGENT = 2;
    const USER_TYPE_EXTERNAL_AGENT = 3;
    const USER_TYPE_CUSTOMER = 4;


    public function __construct($template, $userType, $to, array $params)
    {
        $this->template = $template;
        $this->member_type_id = $userType;
        $this->to = $to;
        $this->params = $params;
    }

    private function constructBody($link)
    {
        $mobile_number = $this->params["mobile_number"] ?? "user";

        $html = "
                    <!DOCTYPE html>
                    <html lang='en'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
                            <link rel='icon' href='/assets/logo.png'>
                            <title>Confirm your account</title>

                        </head>
                        <body>
                            <div style='width: 640px; font-family: Arial, Helvetica, sans-serif; font-size: 11px;'>
                                <img src='/assets/logo.png'/> 
                                <h1>Hello ${mobile_number}</h1>
                                <div align='center'>
                                    <p>
                                        Please confirm your LAMATA_EPURSE account by clicking the link below.
                                    </p>
                                    <a href='${link}'>${link}</a>
                                </div>
                                <p>This example uses <strong>HTML</strong>.</p>
                                <p>ISO-8859-1 text: éèîüçÅñæß</p>
                            </div>
                        </body>
                    </html>
                            ";


        $text = "
                    Hello ${mobile_number},
            
                    Please confirm your LAMATA_EPURSE account by clicking the link below.

                    ${link}
                ";


        return ["html" => $html, "text" => $text];
    }

    private function getUser()
    {
        switch ($this->userType) {
            case '0':
                $user = 'admin';
                return $user;
            case '1':
                $user = 'organization';
                return $user;
            case '2':
                $user = 'customer';
                return $user;
            default:
                return "";
        }
    }

    private function getTemplate()
    {
        $user = $this->getUser();


        switch ($this->template) {
            case '1':
                $subject = 'Confirm your account';
                $link = "localhost/LAMATA_EPURSE/v1/${user}/update/verification/email/" . $this->params["emailVerificationToken"];
                $body = $this->constructBody($link);
                return ["subject" => $subject, "body" => $body];

            default:
                return ["subject" => "", "body" => ""];
        }
    }

    private function constructMail()
    {

        ["subject" => $subject, "body" => $body] = $this->getTemplate();

        $this->mail = new Mailer();
        $this->mail->from = $this->from;
        $this->mail->fromName = $this->fromName;
        $this->mail->to = $this->to;
        $this->mail->toName = $this->params["mobile_number"];
        $this->mail->subject = $subject;
        $this->mail->htmlBody = $body["html"];
        $this->mail->textBody = $body["text"];
    }

    public function sendMail()
    {
        $this->constructMail();
        return $this->mail->sendMail();
    }
}
