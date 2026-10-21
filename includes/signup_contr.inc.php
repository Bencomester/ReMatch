<?php

declare(strict_types=1);

require_once __DIR__ . "/../mail.php";
require_once __DIR__ . "/../emails/verify.php";
require_once __DIR__ . "/dhb.inc.php";
require_once __DIR__ . "/signup_model.inc.php";

function is_email_invalid(string $email) : bool {
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_username_taken(string $username, $id = 0) : bool {
    $data = get_username($username);
    return $id == 0 ? !empty($data) : (!empty($data) && $data["id"] != $id);
}

function is_username_too_short(string $username) : bool
{
    return strlen($username) < 3;
}

function is_phone_invalid(string $phone) : bool {
    if ($phone == "") return false;
    return !preg_match('/^\+?[0-9\s]{10,15}$/', $phone);
}

function is_phone_taken(string $phone, $id = 0) : bool {
    if ($phone == "") return false;
    $data = get_phone($phone);
    return $id == 0 ? !empty($data) : (!empty($data) && $data["id"] != $id);
}

function is_pwd_not_the_same(string $pwd,string $pwd2) : bool {
    return $pwd!=$pwd2;
}

function is_password_strong($password) : string {
    if (strlen($password) < 8) {
        return "pwd-8-chars";
    }

    if (!preg_match('/[a-z]/', $password)) {
        return "pwd-lower";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return "pwd-upper";
    }

    if (!preg_match('/[0-9]/', $password)) {
        return "pwd-number";
    }

    return "Strong";
}

function is_email_taken(string $email, $id = 0) : bool {
    if (inVerification($email)) return true;
    $data = get_email($email);
    return $id == 0 ? !empty($data) : (!empty($data) && $data["id"] != $id);
}

function inVerification($email) : bool {
    $query = "SELECT email FROM email_verifications WHERE email = :email";
    $value = ["email" => $email];
    return !empty(returnQuery($query, $value));
}

function createUserAndSendEmail(string $username, string $pwd, string $email, string $phone, string $firstname, string $lastname) : void {
    set_user($username, $pwd, $email, $phone, $firstname, $lastname);
    sendVerificationEmail($email, $username);
}

function sendVerificationEmail(string $email, string $username="") : void
{
    try {
        $token = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        die($e->getMessage());
    }

    if ($username == "") {
        $query = "SELECT username FROM users WHERE email = :email";
        $value = [":email" => $email];
        $username = returnQuery($query, $value)["username"];
    }

    $query = "SELECT id FROM users WHERE username = :username";
    $value = ["username" => $username];
    $id = returnQuery($query, $value)["id"];

    if (inVerification($email)) {
        $query = "DELETE FROM email_verifications WHERE email = :email";
        $value = [":email" => $email];
        doQuery($query, $value);
    }

    $expiry = 60*60*2; //2h
    $query = "INSERT INTO email_verifications (email, token, expiry, userid) VALUES (:email, :token, :expiry, :userid)";
    $value = [":email" => $email, ":token" => $token, "userid" => $id, ":expiry" =>  $expiry + (int) date('U')];
    doQuery($query, $value);

    $verificationLink = "http://server/rematch/verify.php?email=$email&token=$token";
    sendMail($email, "Email Verification", verificationEmail($username, $verificationLink));
}