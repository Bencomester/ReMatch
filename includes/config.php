<?php




function startSession() : void {
    if(session_status() === PHP_SESSION_ACTIVE) return;
        ini_set('session.use_only_cookies',1);
    ini_set('session.use_strict_mode',1);
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    ini_set('session.cookie_samesite', 'Strict');

    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24,
        'domain' => 'server',  //Change to public domain
        'path' => '/',
        'secure' => false,     //Change to true, when in production
        'httponly' => true,
        'samesite' => 'Strict'    //Type of third-party use of cookies (I think xd)
    ]);

    session_start();
}
startSession();

if(!isset($_SESSION['last_regeneration'])){
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}else{
    $interval = 60*30;
    if(time()-$_SESSION['last_regeneration']>=$interval){
        session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
    }
}

$timeout_duration = 60 * 60 * 24;  //Set to 24h

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ..");
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();