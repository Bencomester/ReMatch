<?php

require_once __DIR__ . "/dhb.inc.php";
require_once __DIR__ . "/../mail.php";
require_once __DIR__ . "/../emails/login.php";
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/config.php";

unset($_SESSION["login-error"]);
if($_SERVER["REQUEST_METHOD"]=="POST" && !isset($_SESSION["userid"])) {
    if (!validateCSRFToken($_POST["csrf_token"])) {
        $_SESSION["csrf-fail"] = true;
        mainPage();
        exit();
    }
    $username=$_POST["username"];
    $pwd = $_POST["password"];
    $turnstileResponse = $_POST['cf-turnstile-response'];
    if (!cloudflareTurnstile($turnstileResponse)) die("Verification failed");
    $data = usernameQuery($username);

    if(empty($data)){
        $data = emailQuery($username);
    }
    if(password_verify($pwd, $data["pwd"])){
        if ($data["verified"]==1){
            if (checkRateLimit(getUserIP(), "login")) {

                $_SESSION['userid'] =  $data["id"];
                $enabled = returnQuery("SELECT enabled FROM 2fa WHERE userid = :userid", ["userid" => $data["id"]]);
                if (empty($enabled)) {
                    $enabled = 0;
                } else $enabled = $enabled["enabled"];

                if ($enabled == 1) {

                    header("Location: " . str_replace("/var/www/html", "", __DIR__) . "/../2fa.php");
                } else {
                    loginSuccessful($data);
                    exit();
                }
            }
            die("You have been rate limited.");
        } else {
            $_SESSION["login-error"]= "email-not-verified";
            $_SESSION["username"] = $username;
            mainPage();
        }
    }else{
        $_SESSION["login-error"]= "login-error";
        $_SESSION["username"] = $username;
        mainPage();
    }
}

function loginSuccessful($data) : void {
    $userid = $data["id"];
    resetSessionAndCookieTime();
    date_default_timezone_set("UTC");
    $date = date("Y-m-d H:i:s");
    $query="INSERT INTO login (userid, date) values (:id, :date);";
    $param = [":id" => $userid, ":date" => $date];
    doQuery($query, $param);
    setSessionData($userid);
    sendMail($data["email"], "Successful login", loginEmail($data["username"], date("Y-m-d H:i:s e")));
    header("Location: ../myprofile");
}

function mainPage() : void {
    header("Location: ..");
}

function usernameQuery($username) {
    $query="SELECT pwd, id, username, email, verified FROM users WHERE LOWER(username) = :username";
    $param = [":username" => strtolower($username)];
    return returnQuery($query, $param);
}

function emailQuery($email) {
    $query="SELECT pwd, id, username, email, verified FROM users WHERE email = :email";
    $param = [":email" => strtolower($email)];
    return returnQuery($query, $param);
}

function useridQuery($userid) {
    $query="SELECT pwd, id, username, email, verified FROM users WHERE id = :userid";
    $param = [":userid" => $userid];
    return returnQuery($query, $param);
}

function setSessionData ($userid) : void {
    $_SESSION["logged-in"] = true;
    $query="SELECT username, email, firstname, lastname FROM users WHERE id = :id";
    $param = [":id" => $userid];
    $data = returnQuery($query, $param);
    $_SESSION['userid'] = $userid;
    $_SESSION['firstname'] = $data["firstname"];
    $_SESSION['lastname'] = $data["lastname"];
    $_SESSION['email'] = $data["email"];
    $_SESSION['username'] = $data["username"];
}

function cloudflareTurnstile($turnstileResponse) : bool
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
    return true;
    //return $responseKeys['success'];
}

function checkRateLimit($ip, $action, $limit = 10, $timeframe = 300) : bool {
    $query = "SELECT COUNT(*) as attempts FROM rate_limits 
              WHERE ip = :ip AND action = :action 
              AND timestamp > DATE_SUB(NOW(), INTERVAL :timeframe SECOND)";

    $attempts = returnQuery($query, [
        ':ip' => $ip,
        ':action' => $action,
        ':timeframe' => $timeframe
    ])['attempts'];

    if ($attempts >= $limit) {
        return false;
    }

    $query = "INSERT INTO rate_limits (ip, action, timestamp) 
              VALUES (:ip, :action, NOW())";
    doQuery($query, [':ip' => $ip, ':action' => $action]);

    return true;
}

function resetSessionAndCookieTime() : void
{
    $session = $_SESSION;

    if (ini_get("session.use_cookies")) {
        if (!session_name()) die("Session name is invalid.");
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }

    session_destroy();
    startSession();
    $_SESSION = $session;
}

function is2faEnabled($userid) : bool {
    return !empty(returnQuery("SELECT userid FROM 2fa WHERE userid = :userid", ["userid" => $userid]));
}
