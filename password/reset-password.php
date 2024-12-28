<?php

require_once "model.php";
require_once "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkRateLimit("pwdreset")) die("You have been rate limited");
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

