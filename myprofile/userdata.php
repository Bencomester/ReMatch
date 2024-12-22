<?php
require_once(__DIR__ . "/../includes/dhb.inc.php");

function getpfp($userid) : string {
    return file_exists("../pfp/" . $userid . ".png") ? "pfp/" . $userid . ".png" : (file_exists("../pfp/" . $userid . ".jpg") ? "pfp/" . $userid . ".jpg" : "pfp/placeholder.png");
}

function getData($userid) : array {
    $query = "SELECT * FROM users WHERE id = :userid";
    return returnQuery($query, ["userid" => $userid]);
}