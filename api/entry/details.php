<?php
header("Content-Type: application/json");

// Handle CORS for cross-origin requests (optional but recommended)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


require_once "../tokenHandler.php";
require_once "../../includes/entry.inc.php";
$en = include "../../lang/en.php";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $decodedJWT = authenticateJWT();
    if (!empty($decodedJWT)) {
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data["id"];

        $code = "action-successful";
        $id = $decodedJWT->userid;
        echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code],
            "entries" => getEntryDetails($id)
        ]);
    } else {
        $code = "invalid-or-expired-token";
        echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}
