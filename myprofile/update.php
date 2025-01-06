<?php

require_once "../includes/config.php";
require_once "../includes/userdata.inc.php";
require_once('../includes/security.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['logged-in'])) {
        header("Location: ..");
        exit();
    }
    if (!checkRateLimit("signup")) die("You have been rate limited");
    if (!validateCSRFToken($_POST["csrf_token"])) die("Invalid CSRF Token");
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'] ?? "";
    $username = $_POST['username'];
    $email = strtolower($_POST['email']);
    $phone = $_POST['telephone'] ?? "";
    $id = $_SESSION["userid"];

    $errors = [];

    $phpFileUploadErrors = [                                        // TODO: Create codes for each one and add to language file
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'file-error-limit',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
        10 => 'file-error-type',
        11 => 'file-error-save'
    ];
    // Check if the file was uploaded
    if (isset($_FILES["profile-picture"])) {
        if (($_FILES["profile-picture"]["error"] ?? 0) == 0) {
            $code = handlePicture($_FILES["profile-picture"]);
            if ($code != 0) $errors[] = $code;
        } else {
            // if ($_FILES["profile-picture"]["error"] != 4) $errors[] = $phpFileUploadErrors[$_FILES["profile-picture"]["error"]];
            if ($_FILES["profile-picture"]["error"] != 4) $errors[] = "file-error";
        }
    }
    $pwd = $_POST["pwd"] ?? "";
    if ($pwd != $_POST["pwd2"]) $errors[] = "password-do-not-match";
    $errors = array_merge($errors, getErrors($id, $email, $username, $phone, $pwd));

    if (!empty($errors)) {
        $_SESSION["edit-error"] = $errors;
        header("Location: edit.php");
        die();
    }

    updateProfileDetails($id, $email, $username, $phone, $fname, $lname, $pwd);

    header("Location: edit.php?success=true");
} else {
    header("Location: .");
}