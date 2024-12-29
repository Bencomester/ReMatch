<?php
require_once "config.php";
require "dhb.inc.php";
function createEntry($item, $price, $amount, $id, $lon, $lat): void
{
    for ($i = 0; $i < count($item); $i++) {
        $query = "INSERT INTO blogs (item, number, price, blog_id) values (:items, :number, :price, :blog_id);";
        $param = [":items" => $item[$i], ":number" => $amount[$i], ":price" => $price[$i], ":blog_id" => $id];
        doQuery($query, $param);
    }
    $query = "INSERT INTO blog (userid, blog_id, timestamp, lon, lat) values (:userid, :blog_id, unix_timestamp(), :lon, :lat);";
    $param = [":userid" => $_SESSION["userid"], ":blog_id" => $id, ":lon" => $lon, ":lat" => $lat];
    doQuery($query, $param);
}

function generateId(): int
{
    $query = "SELECT blog_id FROM blog;";
    $blog_ids = [];
    foreach (returnQueryAll($query) as $row) $blog_ids[] = $row["blog_id"];
    $id = 1;
    while (true) {
        if (!in_array($id, $blog_ids)) break;
        $id++;
    }
    return $id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $items = $_POST["item"];
    $number = $_POST["number"];
    $price = $_POST["price"];
    $lon = $_POST["long"];
    $lat = $_POST["lat"];
    createEntry($items, $price, $number, generateid(), $lon, $lat);
    header("Location: ../rematch.php?create=success");
    exit();
}