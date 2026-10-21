<?php
require_once __DIR__ . '/../vendor/autoload.php';  // Composer's autoloader
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$env = require __DIR__ . '/../includes/env.php';
$secretKey = $env['JWT_SECRET'];

function generateJWT($userid): string
{
    global $secretKey;
    $issuedAt = time();
    $payload = [
        'iat' => $issuedAt,
        'userid' => $userid
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

    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        http_response_code(401);
        echo json_encode(['message' => 'Token not found']);
        exit();
    }

    try {
        $decodedJWT = JWT::decode($jwt, new Key($secretKey, 'HS256'));

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


