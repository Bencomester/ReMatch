<?php
require '../vendor/autoload.php';  // Composer's autoloader
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = "secret"; //Change to something secure

function generateJWT($userid) : string {
    global $secretKey;
    $issuedAt = time();  // When the token is issued
    $payload = [
        'iat' => $issuedAt,        // Issued at: time the token was generated
        'userid' => $userid        // User ID or any other information you want to store
    ];
    return JWT::encode($payload, $secretKey, 'HS256');
}

function authenticateJWT() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    global $secretKey;
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(['message' => 'Authorization header not found']);
        exit();
    }

    // Extract the JWT from the "Bearer" part of the Authorization header
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        http_response_code(401);
        echo json_encode(['message' => 'Token not found']);
        exit();
    }

    try {
        // Decode and verify the token
        $decodedJWT = JWT::decode($jwt, new Key($secretKey, 'HS256'));  // Return the decoded token payload

        $pwdDate = returnQuery("SELECT UNIX_TIMESTAMP(pwdDate) FROM users WHERE id = :userid", ["userid"=>($decodedJWT->userid)])["UNIX_TIMESTAMP(pwdDate)"];
        if (empty($pwdDate) || $decodedJWT->iat < $pwdDate) {
            echo json_encode(['message' => 'Access denied', 'cause' => 'Expired token']);
            return [];
        } else {
            return $decodedJWT;
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid token: ' . $e->getMessage()]);
        exit();
    }
}


