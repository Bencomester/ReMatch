<?php
header("Content-Type: application/json");

// Handle CORS for cross-origin requests (optional but recommended)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


require_once "tokenHandler.php";
require_once "../includes/dhb.inc.php";
require_once '../myprofile/userdata.php';
$en = include "../lang/en.php";

if($_SERVER["REQUEST_METHOD"]=="GET") {
    $decodedJWT = authenticateJWT();
    if (!empty($decodedJWT)) {
        $code = "auth-success";
        $id = $decodedJWT->userid;
        $data = getData($id);
        $imageData = file_get_contents(__DIR__ . "/../" . getpfp($id));
        $base64Image = base64_encode($imageData);
        //$jsonReadyImage = 'data:image/png;base64,' . $base64Image;
        echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code],
                "userid" => $id,
                "username" => $data["username"],
                "firstname" => $data["firstname"],
                "lastname" => $data["lastname"],
                "email" => $data["email"],
                "phone" => $data["phone"],
                "pfp" => $base64Image
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
