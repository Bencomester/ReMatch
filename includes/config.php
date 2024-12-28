<?php

$regenerationInterval = 60 * 30;              // 30 minutes
$timeoutDuration = 60 * 60 * 24;              // 24 hours

function startSession($timeout): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', $timeout);
    ini_set('session.cookie_lifetime', $timeout);
    ini_set('session.cookie_samesite', 'Strict');

    session_set_cookie_params([
        'lifetime' => $timeout,
        'domain' => '[REDACTED_LOCAL_IP]',  //Change to public domain
        'path' => '/',
        'secure' => true,     //Made self-signed ssl cert for local ip
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();

    // Initialize timestamps if not set
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    }
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
}

startSession($timeoutDuration);

// Regenerate session ID after the interval
if (time() - $_SESSION['last_regeneration'] >= $regenerationInterval) {
    session_regenerate_id(true); // Secure regeneration
    $_SESSION['last_regeneration'] = time();
}

// Check for session timeout
if ((time() - $_SESSION['last_activity']) > $timeoutDuration) {
    session_unset();
    session_destroy();
    header("Location: ..");
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
