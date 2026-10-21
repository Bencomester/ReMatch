<?php

$regenerationInterval = 60 * 30;              // 30 minutes
$timeoutDuration = 60 * 60 * 24;              // 24 hours

function startSession($timeout): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $env = require __DIR__ . '/env.php';
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', $timeout);
    ini_set('session.cookie_lifetime', $timeout);
    ini_set('session.cookie_samesite', 'Strict');

    session_set_cookie_params([
        'lifetime' => $timeout,
        'domain' => $env['COOKIE_DOMAIN'],
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();

    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    }
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
}

startSession($timeoutDuration);

if (time() - $_SESSION['last_regeneration'] >= $regenerationInterval) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

if ((time() - $_SESSION['last_activity']) > $timeoutDuration) {
    session_unset();
    session_destroy();
    header("Location: ..");
    exit();
}

$_SESSION['last_activity'] = time();
