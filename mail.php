<?php

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


function sendMail($email, $subject, $body) : void
{
    $mail = new PHPMailer(true);
    $env = require __DIR__ . '/includes/env.php';
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USER'];
        $mail->Password = $env['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($env['SMTP_USER'], 'Rematch Do-Not-Reply');
        $mail->addAddress($email);

        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $body;

        $mail->send();
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

