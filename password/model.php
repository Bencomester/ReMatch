<?php

require_once '../includes/dhb.inc.php';
require_once '../mail.php';
require_once '../emails/reset.php';

function getEmail($email)
{
    $query = "SELECT id FROM users WHERE email = :email";
    $value = ["email" => $email];
    return returnQuery($query, $value);
}

function inReset($email) : bool {
    $query = "SELECT email FROM password_resets WHERE email = :email";
    $value = ["email" => $email];
    return !empty(returnQuery($query, $value));
}

function genTokenAndSendEmail($email) : void {
    // User exists, generate a token
    try {
        $token = bin2hex(random_bytes(50));
    } catch (Exception $e) {
        die($e->getMessage());
    } // Generate a random token
    $expires = 60 * 30; // Token expires in 30 minutes

    //Delete if already exists
    if (inReset($email)) {
        $query = "DELETE FROM password_resets WHERE email = :email";
        $value = [":email" => $email];
        doQuery($query, $value);
    }

    // Insert token into the password_reset table (create this table in your DB)
    $query = "INSERT INTO password_resets (email, token, expiry) VALUES (:email, :token, :expiry)";
    $value = [":email" => $email, ":token" => $token, ":expiry" =>  $expires + (int) date('U')];
    doQuery($query, $value);

    // Send the password reset email
    $resetLink = "http://server/rematch/password/reset.php?token=" . $token;  //Change to domain
    $subject = "Password Reset Request";
    //$message = "<p>Click the following link to reset your password: $resetLink</p><p>The link is active for " . $expires/60 . " minutes.</p>";
    $message = resetEmail($resetLink, $expires/60);
    sendMail($email, $subject, $message);
}