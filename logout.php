<?php
require_once "includes/config.php";

// Unset all session variables
$_SESSION = array();

// If the session uses cookies, delete the session cookie as well
if (ini_get("session.use_cookies")) {
    if (!session_name()) die("Session name is invalid.");
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

session_unset();
session_destroy();

// Redirect to login page
header("Location: .");
exit();
