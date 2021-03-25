<?php

namespace Liveet\Domain;

use Rashtell\Domain\Mailer;

class MailHandler
{
    const TEMPLATE_CONFIRM_EMAIL = 1;
    const TEMPLATE_FORGOT_PASSWORD = 2;

    const USER_TYPE_ADMIN = "admins";
    const USER_TYPE_ORGANISER = "organiser";
    const USERTYPE_ORGANISER_STAFF = "organiser_staff";

    public $from = "info@liveet.com";
    public $fromName = "Liveet";
    private $template = "";
    private  $usertype = "";
    private $to = "";
    private $params = "";

    public function __construct($template, $usertype, $to, array $params)
    {
        $this->template = $template;
        $this->usertype = $usertype;
        $this->to = $to;
        $this->params = $params;
    }

    private function createConfirmEmailBody($link)
    {
        $username = $this->params["username"] ?? "user";

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
                                <h1>Hello {$username}</h1>
                                <div align='center'>
                                    <p>
                                        Please confirm your liveet account by clicking the link below.
                                    </p>
                                    <a href='{$link}'>Click here to verify your email address</a>
                                </div>
                            </div>
                        </body>
                    </html>
                            ";


        $text = "Hello {$username},\n\nPlease confirm your Lagos-recycle account by clicking the link below.\n{$link}";


        return ["html" => $html, "text" => $text];
    }

    private function createForgotPasswordBody($link)
    {
        $username = $this->params["username"] ?? "user";

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
                                <h1>Hello {$username}</h1>
                                <div align='center'>
                                    <p>
                                        Please confirm your liveet account by clicking the link below.
                                    </p>
                                    <a href='{$link}'>Click here to verify your email address</a>
                                </div>
                            </div>
                        </body>
                    </html>
                            ";


        $text = "Hello {$username},\n\nPlease confirm your Lagos-recycle account by clicking the link below.\n{$link}";


        return ["html" => $html, "text" => $text];
    }

    private function getTemplate()
    {
        $body = "";
        $subject = "";

        switch ($this->template) {
            case self::TEMPLATE_CONFIRM_EMAIL:
                $subject = "Confirm your account";

                $link = "https://" . Constants::PRODUCTION_HOST . $_ENV["BASE_PATH"] . "/" . $this->usertype . "/update/verify/email/" . $this->params["email_verification_token"];

                $body = $this->createConfirmEmailBody($link);
                break;

            case self::TEMPLATE_FORGOT_PASSWORD:
                $subject = "Reset your password";

                $link = "https://" . Constants::PRODUCTION_HOST . $_ENV["BASE_PATH"] . "/" . $this->usertype . "/forgot/password/" . $this->params["forgotPasswordToken"];

                $body = $this->createForgotPasswordBody($link);
                break;
            default:
                break;
        }

        return ["subject" => $subject, "body" => $body];
    }

    private function constructMail()
    {

        ["subject" => $subject, "body" => $body] = $this->getTemplate();

        $mail = new Mailer();
        $mail->from = $this->from;
        $mail->fromName = $this->fromName;
        $mail->to = $this->to;
        $mail->toName = $this->params["username"];
        $mail->subject = $subject;
        $mail->htmlBody = $body["html"];
        $mail->textBody = $body["text"];

        return $mail;
    }

    public function sendMail()
    {
        ["subject" => $subject, "body" => $body] = $this->getTemplate();

        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        // Additional headers
        $headers[] = 'To: ' . $this->params["username"] . ' <' . $this->to . '>';
        $headers[] = 'From: ' . $this->fromName . ' <' . $this->from . '>';

        // $htmlBody = $body["html"];
        // $textBody = $body["text"];

        $errLevel = error_reporting(E_ALL ^ E_NOTICE);  // suppress NOTICEs

        if (@mail($this->to, $subject, $body["html"], implode("\r\n", $headers))) {

            error_reporting($errLevel);  // restore old error levels
            return ["success" => "Mail sent", "error" => null];
        }

        error_reporting($errLevel);  // restore old error levels
        return ["success" => null, "error" => "Error sending mail"];

        // return $this->constructMail()->sendMail();
    }
}
