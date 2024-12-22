<?php
header("Content-Type: application/json");

// Handle CORS for cross-origin requests (optional but recommended)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../includes/signup_model.inc.php';
require_once '../includes/signup_contr.inc.php';
$en = include "../lang/en.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true); // Get JSON input

    $username = $data["username"] ?? '';
    $pwd = $data["password"] ?? '';
    $email = strtolower($data["email"] ?? '');
    $telephone = $data["telephone"] ?? '';
    $firstname = $data["firstname"] ?? '';
    $lastname = $data["lastname"] ?? '';

    $errors = [];
    $code = "";
    // Client validates: password match
    if (empty($username) || empty($pwd) || empty($email)) {
        $code = "empty-email-username-pwd";
        $errors[$code] = $en[$code];
    }
    if(is_email_invalid($email)){
        $code = "invalid-email";
        $errors[$code] = $en[$code];
    }
    if(is_username_too_short($username)){
        $code = "username-too-short";
        $errors[$code] = $en[$code];
    }
    if(is_username_taken($username)){
        $code = "username-taken";
        $errors[$code] = $en[$code];
    }
    if(is_email_taken($email)){
        $code = "email-taken";
        $errors[$code] = $en[$code];
    }
    if(is_phone_invalid($telephone)){
        $code = "invalid-phone";
        $errors[$code] = $en[$code];
    }
    if(is_phone_taken($telephone)){
        $code = "phone-taken";
        $errors[$code] = $en[$code];
    }

    $strength = is_password_strong($pwd);
    if($strength != "Strong"){
        $code = $strength;
        $errors[$code] = $en[$code];
    }

    if (!empty($errors)) {
        echo json_encode(["status" => "error",  "code" => $code, "message" => $en[$code], "all-codes" => array_keys($errors), "all-messages" => array_values($errors)]);
        //echo json_encode(["status" => "error", "error" => array_values($errors)[0]]);
        exit();
    }

    // Create user
    createUserAndSendEmail($username, $pwd, $email, $telephone, $firstname, $lastname);
    $code = "signup-success";
    echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code]]);
} else {
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}

