<?php

require_once "model.php";

if(session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $data = getEmail($email);

    unset($_SESSION['email_not_found']);
    unset($_SESSION['reset_sent']);

    if (!empty($data)) {
        genTokenAndSendEmail($email);
        $_SESSION['reset'] = "reset-sent";
    } else {
        $_SESSION['reset'] = "email-not-found";
    }
    header('location: .');
}

