<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../password/model.php";
$en = include "../lang/en.php";
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data["email"] ?? "";
    if (!empty(getEmail($email))) {
        genTokenAndSendEmail($email);
        $code = "reset-sent";
        echo json_encode(["status" => "success",  "code" => $code, "message" => $en[$code]]);
    } else {
        $code = "email-not-found";
        echo json_encode(["status" => "error",  "code" => $code, "message" => $en[$code]]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}