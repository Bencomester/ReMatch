<?php

require_once "includes/config.php";
require_once('../includes/dhb.inc.php');
require_once('../includes/signup_contr.inc.php');
require_once('../emails/email.php');
require_once('../mail.php');
require_once('../includes/security.php');
if (!isset($_SESSION['logged-in'])) {
    header("Location: ..");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCSRFToken($_POST["csrf_token"])) die("Invalid CSRF Token");
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'] ?? "";
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['telephone'] ?? "";
    $id = $_SESSION["userid"];

    $errors = [];

    $phpFileUploadErrors = [
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
            if ($code != 0) $errors["file"] = $code;
        } else {
            if ($_FILES["profile-picture"]["error"] != 4) $errors["file"] = $phpFileUploadErrors[$_FILES["profile-picture"]["error"]];
        }
    }

    if (is_email_invalid($email)) $errors["email"] = "invalid-email";
    if (is_username_taken($username, $id)) $errors["username"] = "username-taken";
    if (is_email_taken($email, $id)) $errors["email"] = "email-taken";
    if (is_username_too_short($username)) $errors["username"] = "username-too-short";
    if (is_phone_invalid($phone)) $errors["phone"] = "invalid-phone";
    if (is_phone_taken($phone, $id)) $errors["phone"] = "phone-taken";

    if ($errors) {
        $_SESSION["edit-error"] = $errors;
        header("Location: edit.php");
        die();
    }

    $query = "SELECT email FROM users WHERE id = :userid";
    $oldEmail = returnQuery($query, [":userid" => $id])["email"];
    if ($oldEmail != $email) sendEmailAddressChange($email, $username, $id);

    $query = "UPDATE users SET firstname = :fname, lastname = :lname, username = :username, phone = :phone WHERE id = :userid";
    $params = ["fname" => $fname, "lname" => $lname, "username" => $username, "phone" => $phone, "userid" => $_SESSION["userid"]];
    doQuery($query, $params);

    header("Location: edit.php?success=true");
} else {
    header("Location: .");
}

function sendEmailAddressChange($email, $username, $id): void
{
    try {
        $token = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        die($e->getMessage());
    }

    if (inVerification($email)) {
        $query = "DELETE FROM email_verifications WHERE email = :email";
        $value = [":email" => $email];
        doQuery($query, $value);
    }

    $expiry = 60 * 60 * 4; //4h
    $query = "INSERT INTO email_verifications (email, token, expiry, userid) VALUES (:email, :token, :expiry, :userid)";
    $value = [":email" => $email, ":token" => $token, "userid" => $id, ":expiry" => $expiry + (int)date('U')];
    doQuery($query, $value);

    $link = "http://server/rematch/change-email.php?email=$email&token=$token";
    sendMail($email, "Confirm e-mail address change", emailChange($username, $link));
}

function handlePicture($pfp): int
{
    // Define allowed file types
    $allowedTypes = ["image/jpeg", "image/png"];
    $maxFileSize = 50 * 1024 * 1024; // 50MB max file size

    // Get file info
    $fileTmpPath = $pfp["tmp_name"];
    $fileName = $pfp["name"];
    $fileSize = $pfp["size"];
    $fileType = $pfp["type"];

    // Get the file extension
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Generate a new unique file name (to avoid overwriting)
    $newFileName = $_SESSION["userid"] . "." . $fileExtension;

    // Define the target directory to save the image
    $uploadDir = "../pfp/";

    // Validate file type and size
    if (!in_array($fileType, $allowedTypes)) {
        echo "Error: Only JPG and PNG files are allowed.";
        return 2;
    } else if ($fileSize > $maxFileSize) {
        echo "Error: File size exceeds the 50MB limit.";
        return 11;
    } else {
        // Move the file from temporary folder to the target directory
        $uploadFilePath = $uploadDir . $newFileName;
        $test = $uploadDir . $_SESSION["userid"] . ".";
        $extensions = ["jpeg", "jpg", "png"];
        foreach ($extensions as $ext) {
            if (file_exists($test . $ext)) unlink($test . $ext);
        }

        if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
            cropImageToSquare($uploadFilePath);
            resizeImage($uploadFilePath, 150);
            echo "File successfully uploaded to: " . $uploadFilePath;
            return 0;
        } else {
            echo "Error: There was a problem uploading the file.";
            return 11;
        }
    }
}

function resizeImage($sourcePath, $newWidth, $newHeight = 0, $destinationPath = ""): void
{
    if ($newHeight == 0) $newHeight = $newWidth;
    if ($destinationPath == "") $destinationPath = $sourcePath;
    // Get the original image dimensions and type
    list($width, $height, $type) = getimagesize($sourcePath);

    // Create a new blank image with the desired dimensions
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Load the source image based on its type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            echo "Unsupported image type.";
            return;
    }

    // Resize the original image and copy it to the new image
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save the resized image to the destination path
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $destinationPath, 90); // 90 is the quality for JPEG
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $destinationPath);
            break;
        case IMAGETYPE_GIF:
            imagegif($newImage, $destinationPath);
            break;
    }

    // Free up memory
    imagedestroy($newImage);
    imagedestroy($sourceImage);
}

function cropImageToSquare($sourcePath, $destinationPath = ""): void
{
    if ($destinationPath == "") $destinationPath = $sourcePath;
    // Load the image
    $imageInfo = getimagesize($sourcePath);
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $imageType = $imageInfo[2];

    // Determine the image type
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($sourcePath);
            break;
        default:
            die('Unsupported image type.');
    }

    // Calculate the size of the square
    $size = min($width, $height);
    $xOffset = ($width > $height) ? ($width - $height) / 2 : 0;
    $yOffset = ($height > $width) ? ($height - $width) / 2 : 0;

    // Create a blank square canvas
    $squareImage = imagecreatetruecolor($size, $size);

    // Copy and crop the image
    imagecopyresampled(
        $squareImage, $image,
        0, 0, // Destination x, y
        $xOffset, $yOffset, // Source x, y
        $size, $size, // Destination width, height
        $size, $size // Source width, height
    );

    // Save the cropped image
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($squareImage, $destinationPath, 90); // Quality: 0-100
            break;
        case IMAGETYPE_PNG:
            imagepng($squareImage, $destinationPath);
            break;
        case IMAGETYPE_GIF:
            imagegif($squareImage, $destinationPath);
            break;
    }

    // Clean up memory
    imagedestroy($image);
    imagedestroy($squareImage);
}