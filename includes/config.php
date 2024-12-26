<?php
function startSession() : void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    ini_set('session.cookie_samesite', 'Strict');

    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24,
        'domain' => '[REDACTED_LOCAL_IP]',  //Change to public domain
        'path' => '/',
        'secure' => true,     //Made self-signed ssl cert for local ip
        'httponly' => true,
        'samesite' => 'Strict'    //Type of third-party use of cookies (I think xd)
    ]);

    session_start();
    $_SESSION['last_regeneration'] = time();
    $_SESSION['last_activity'] = time();
}

startSession();

// Update last activity timestamp
$_SESSION['last_activity'] = time();

$regenerationInterval = 60 * 30; //Set to 30 min
function regenerateSessionId(): void
{
    $session = $_SESSION;
    session_regenerate_id(true);
    /** @noinspection PhpConditionAlreadyCheckedInspection */
    $_SESSION = $session;
    $_SESSION['last_regeneration'] = time();
}

if (time() - $_SESSION['last_regeneration'] >= $regenerationInterval) regenerateSessionId();

$timeout_duration = 60 * 60 * 24;  //Set to 24h
function deleteSession(): void
{
    session_unset();
    session_destroy();
    header("Location: ..");
}

if ((time() - $_SESSION['last_activity']) > $timeout_duration) deleteSession();

