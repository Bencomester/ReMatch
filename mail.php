<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendMail($email, $subject, $body) : void
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rematch.auto.info@gmail.com'; // Your Gmail address
        $mail->Password = '[REDACTED_GMAIL_APP_PASSWORD]';   // App Password or Gmail password if 2FA is not enabled
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('rematch.auto.info@gmail.com', 'Rematch Do-Not-Reply');
        //$mail->addAddress($email);  // Add recipient email
        $mail->addAddress("[REDACTED_EMAIL]");  //Change this back to $email

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $body;

        // Send email
        $mail->send();
        //echo 'Email has been sent successfully!';
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

