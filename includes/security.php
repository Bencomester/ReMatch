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
