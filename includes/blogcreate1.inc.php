<?php
require_once "config.php";
require "dhb.inc.php";
function createEntry($item, $price, $amount, $id): void
{
    for ($i = 0; $i < count($item); $i++) {
        $query = "INSERT INTO blogs (item, number, price, blog_id) values (:items, :number, :price, :blog_id);";
        $param = [":items" => $item[$i], ":number" => $amount[$i], ":price" => $price[$i], ":blog_id" => $id];
        doQuery($query, $param);
    }
    $query = "INSERT INTO blog (userid, blog_id) values (:userid, :blog_id);";
    $param = [":userid" => $_SESSION["userid"], ":blog_id" => $id];
    doQuery($query, $param);
}

function console_log($output, $with_script_tags = true): void
{
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
        ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
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
    console_log($items);            //   I guess we can keep
    console_log($number);           //   this for debugging
    console_log($price);            //   reasons, for now
    createEntry($items, $price, $number, generateid());
    header("Location: ../rematch.php?create=success");
    exit();
}