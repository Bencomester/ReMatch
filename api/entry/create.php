<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../../includes/entry.inc.php";
require_once "../tokenHandler.php";
$en = include "../../lang/en.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $decodedJWT = authenticateJWT();
    if (!empty($decodedJWT)) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($lat) || empty($lon)) {
            $code = "missing-location";
            echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
            exit();
        }
        $lat = $data["lat"];
        $lon = $data["lon"];

        $code = "action-successful";
        $id = $decodedJWT->userid;

        createEntry($decodedJWT->userid, $data["plastic"] ?? 0, $data["glass"] ?? 0, $data["aluminium"] ?? 0, $lon, $lat);
        echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code]]);
    } else {
        $code = "invalid-or-expired-token";
        echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}

