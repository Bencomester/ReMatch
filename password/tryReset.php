<?php
require_once "includes/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    unset($_SESSION["reset_success"]);
    unset($_SESSION["reset_error"]);
    if ($newPassword !== $confirmPassword) {
        $_SESSION["reset_error"] = "Passwords do not match.";
        header("Location: reset.php?token=" . $token);
    }

    // Check if the token is valid
    require '../includes/dhb.inc.php';
    $query = "SELECT email FROM password_resets WHERE token = :token AND expiry >= :now";
    $values = ["token" => $token, "now" => date('U')];
    $data = returnQuery($query, $values);

    if ($data) {
        // Token is valid, update the user's password
        $email = $data['email'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $query = "UPDATE users SET pwd = :password, pwdDate = :date WHERE email = :email";
        date_default_timezone_set("UTC");
        $values = ["password" => $hashedPassword, "email" => $email, "date" => date("Y-m-d H:i:s")];
        doQuery($query, $values);

        // Delete the token
        $query = "DELETE FROM password_resets WHERE email = :email";
        $values = ["email" => $email];
        doQuery($query, $values);

        $_SESSION["reset_success"] = "password-has-been-reset";
    } else {
        $_SESSION["reset_error"] = "invalid-or-expired-token";
    }
    header("Location: reset.php?token=" . $token);
}

