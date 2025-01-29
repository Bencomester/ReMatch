<?php
require_once "../includes/entry.inc.php";
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = $_POST["item"];
    $number = $_POST["number"];
    $price = $_POST["price"];
    $lon = $_POST["long"];
    $lat = $_POST["lat"];
    $items = [];
    for ($i = 0; $i < count($item); $i++) {
        $items[] = ["item" => $item[$i], "amount" => $number[$i], "price" => $price[$i]];
    }

    createEntry($_SESSION["userid"], $items, $lon, $lat);
    header("Location: .?create=success");
    exit();
}