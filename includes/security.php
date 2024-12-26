<?php

function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function cloudflareTurnstile($turnstileResponse): bool
{
    $secretKey = '[REDACTED_CLOUDFLARE_TURNSTILE_KEY]';

    // Verify the response with Cloudflare's API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $secretKey,
        'response' => $turnstileResponse
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseKeys = json_decode($response, true);
    return $responseKeys['success'];
}
