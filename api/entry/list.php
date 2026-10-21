<?php
header("Content-Type: application/json");
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

        $search = $data["search"] ?? "";
        $sort = $data["sort"] ?? "id";
        $desc = $data["descending"] ?? false;
        $limit = $data["limit"] ?? 20;
        $lat = $data["lat"] ?? 0;
        $lon = $data["lon"] ?? 0;

        $code = "action-successful";
        $id = $decodedJWT->userid;
        echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code],
            "entries" => getEntriesSearchAndSort($search, $sort, $desc, $limit, $lat, $lon)
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

