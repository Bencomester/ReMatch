<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


require_once "tokenHandler.php";
require_once "../includes/dhb.inc.php";
$en = include "../lang/en.php";

if($_SERVER["REQUEST_METHOD"]=="GET") {
    $decodedJWT = authenticateJWT();
    if (!empty($decodedJWT)) {
        $code = "auth-success";
        echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code], 'userid' => $decodedJWT->userid]);
    } else {
        $code = "invalid-or-expired-token";
        echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}
