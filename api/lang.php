<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$en = include "../lang/en.php";
if ($_SERVER["REQUEST_METHOD"]=="GET") {
    $data = json_decode(file_get_contents("php://input"), true);
    $lang = $data["lang"] ?? "";
    if ($lang == "hu") {
        $file = include "../lang/hu.php";
    } else {
        $file = $en;
    }

    if (isset($data["code"])) {
        $code = $data["code"];
        echo json_encode(["status" => "success", "code" => "action-successful", "message" => $file[$code]]);
        exit();
    }

    echo json_encode($file);
} else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}