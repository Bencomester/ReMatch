<?php
require_once "../includes/2fa.inc.php";
require_once __DIR__ . "/../includes/config.php";

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    $enabled = (is2faOnAndEnabled($_SESSION["userid"]) ?? 0) == 1;
    $otp = str_replace(" ", "", $_POST["otp"]);
    $error = !run2tfa($otp, !$enabled);
    if ($error) {
        header("Location: ../index.php?enable=failure");
        exit();
    } else {
        if ($enabled) {
            loginSuccessful(useridQuery($_SESSION["userid"]));
            exit();
        }
        header("Location: ../index.php?enable=success");
    }
}