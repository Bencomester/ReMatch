<?php
require_once "../includes/config.php";
require_once "../includes/userdata.inc.php";

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

    require '../includes/dhb.inc.php';
    $query = "SELECT email FROM password_resets WHERE token = :token AND expiry >= :now";
    $values = ["token" => $token, "now" => date('U')];
    $data = returnQuery($query, $values);

    if ($data) {
        $email = $data['email'];

        $query = "SELECT id FROM users WHERE email = :email";
        $id = returnQuery($query, ["email" => $email])["id"];
        resetPassword($id, $newPassword);

        $query = "DELETE FROM password_resets WHERE email = :email";
        $values = ["email" => $email];
        doQuery($query, $values);

        $_SESSION["reset_success"] = "password-has-been-reset";
    } else {
        $_SESSION["reset_error"] = "invalid-or-expired-token";
    }
    header("Location: reset.php?token=" . $token);
}

