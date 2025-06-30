<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Import PHPMailer classes
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'madhan2001ana@gmail.com'; // SMTP email
        $this->mail->Password   = 'ilergdrkkdycocoh'; // SMTP password
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port       = 465;
        //ilergdrkkdycocoh - madhan password

        
        $this->mail->setFrom('madhan2001ana@gmail.com', 'GearSphere');
    }

    // Set email subject and message
    public function setInfo($recipientEmail,$subject, $message) {
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $message;
    }

    // Send the email
    public function send() {
        try {
            $this->mail->send();
            return true;
                } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}
