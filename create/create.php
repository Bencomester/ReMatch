<?php
require_once "../includes/entry.inc.php";
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    createEntry($_SESSION["userid"], $_POST["plastic"], $_POST["glass"], $_POST["aluminium"], $_POST["long"], $_POST["lat"]);
    header("Location: .?create=success");
    exit();
}