<?php
require_once __DIR__ . "/../includes/2fa.inc.php";
require_once __DIR__ . "/../includes/config.php";

if (!isset($_SESSION["logged-in"])) {
    header("Location: ../login");
}

disable2fa($_SESSION["userid"]);
header("Location: ../myprofile?2fa=disabled");