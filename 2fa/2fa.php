<?php
require_once __DIR__ . "/../includes/2fa.inc.php";
require_once __DIR__ . "/../includes/config.php";

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    $enabled = (is2faOnOrEnabled($_SESSION["userid"]) ?? 0) == 1;
    if (!$enabled) exit();
    $otp = str_replace(" ", "", $_POST["otp"] ?? 0);
    if (verify2fa($otp, $_SESSION["userid"])) {
        loginSuccessful(useridQuery($_SESSION["userid"]));
        exit();
    } else {
        header("Location: ./?verify=failed");
    }
}