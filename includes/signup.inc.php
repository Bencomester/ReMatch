<?php
if(session_status() === PHP_SESSION_NONE) session_start();

require_once "signup_model.inc.php";
require_once "signup_contr.inc.php";
require_once 'security.php';

if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "../lang/$lang.php";

if ($_SERVER["REQUEST_METHOD"]==="POST"){
    if (!validateCSRFToken($_POST["csrf_token"])) die("Invalid CSRF Token");
    $username=$_POST["username"];
    $pwd = $_POST["password"];
    $pwd2 = $_POST["password2"];
    $email = strtolower($_POST["email"]);
    //$phone = htmlspecialchars($_POST["telephone"] ?? '', ENT_QUOTES, 'UTF-8');
    $phone = $_POST["telephone"] ?? "";
        $errors=[];

        if(is_pwd_not_the_same($pwd, $pwd2)) $errors["pwd"] = "password-do-not-match";
        if(is_email_invalid($email)) $errors["email"] = "invalid-email";
        if(is_username_taken($username)) $errors["username"] = "username-taken";
        if(is_email_taken($email)) $errors["email"] = "email-taken";
        if(is_username_too_short($username)) $errors["username"] = "username-too-short";
        if(is_phone_invalid($phone)) $errors["phone"] = "invalid-phone";
        if(is_phone_taken($phone)) $errors["phone"] = "phone-taken";

        $strength = is_password_strong($pwd);
        if($strength != "Strong"){
            $errors["password"] = $strength;
        }

        if($errors){
            $_SESSION["error_signup"] = $errors;
            $_SESSION["signup_post"] = $_POST;
            header("Location: ../signup.php");
            die();
        }
        createUserAndSendEmail($username, $pwd, $email, $phone, $_POST["lastname"] ?? "", $_POST["firstname"] ?? "");
        header("Location: ../signup.php?signup=success");
} else {
    header("Location: ../signup.php");
}
die();