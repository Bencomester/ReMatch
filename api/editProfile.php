<?php
header("Content-Type: application/json");

// Handle CORS for cross-origin requests (optional but recommended)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../includes/signup_model.inc.php';
require_once '../includes/signup_contr.inc.php';
require_once "tokenHandler.php";
$en = include "../lang/en.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true); // Get JSON input
    $decodedJWT = authenticateJWT();

    if (!empty($decodedJWT)) {
        $username = $data["username"] ?? '';
        $email = strtolower($data["email"] ?? '');
        $telephone = $data["telephone"] ?? '';
        $firstname = $data["firstname"] ?? '';
        $lastname = $data["lastname"] ?? '';
        $pwd = $data["password"] ?? "";
        $id = $decodedJWT->userid;
        $pfp = $data["pfp"] ?? '';

        $errors = getErrors($id, $email, $username, $telephone, $pwd);
        if ($pfp != "") {
            $response = handlePictureFromBase64($data["pfp"], $id);
            if ($response != 0) $errors[] = "file-error";
        }

        if ($errors) {
            $code = $errors[0];
            echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code], "all-codes" => $errors]);
        } else {
            updateProfileDetails($id, $email, $username, $telephone, $firstname, $lastname, $pwd);
            $code = "action-successful";
            echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code]]);
        }
    } else {
        $code = "invalid-or-expired-token";
        echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}


