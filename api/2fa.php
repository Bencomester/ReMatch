<?php
header("Content-Type: application/json");

// Handle CORS for cross-origin requests (optional but recommended)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . "/tokenHandler.php";
require_once __DIR__ . "/../includes/2fa.inc.php";
$en = include __DIR__ . "/../lang/en.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $data = json_decode(file_get_contents("php://input"), true);

    $decodedJWT = authenticateJWT();
    if (!empty($decodedJWT)) {
        $otp = $data["otp"] ?? 0;
        $action = $data["action"] ?? "";
        $userid = $decodedJWT->userid;
        $enabled = is2faOnOrEnabled($userid);
        if ($action == "disable") {
            if ($enabled == 1) disable2fa($userid);
            $code = "2fa-disabled";
            $error = false;
        } else if ($action == "enable") {
            if ($enabled == -1) {
                $code = "genQR-first";
                $error = true;
            } else {
                if (verify2fa($otp, $userid)) {
                    $code = "2fa-enabled";
                    $error = false;
                } else {
                    $code = "invalid-2fa";
                    $error = true;
                }
            }
        } else if ($action = "genQR") {
            if ($enabled == -1) $qr = create2fa($userid);
            else $qr = getSecretQR($userid);
            $code = "genQR-success";
            echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code], "qr" => $qr]);
            exit();
        } else {
            $code = "invalid-action";
            $error = true;
        }
    } else {
        $code = "invalid-or-expired-token";
        $error = true;
    }
    echo json_encode(["status" => $error ? "error" : "success", "code" => $code, "message" => $en[$code]]);
}
else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}