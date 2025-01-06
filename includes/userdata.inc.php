<?php
require_once __DIR__ . "/dhb.inc.php";
require_once __DIR__ . "/../emails/email.php";
require_once __DIR__ . "/../mail.php";
require_once __DIR__ . "/signup_contr.inc.php";

function getErrors($id, $email, $username, $phone, $password): array
{
    $errors = [];
    if ($password != "" && is_password_strong($password) != "Strong") $errors[] = is_password_strong($password);
    if (is_email_invalid($email)) $errors[] = "invalid-email";
    if (is_username_taken($username, $id)) $errors[] = "username-taken";
    if (is_email_taken($email, $id)) $errors[] = "email-taken";
    if (is_username_too_short($username)) $errors[] = "username-too-short";
    if (is_phone_invalid($phone)) $errors[] = "invalid-phone";
    if (is_phone_taken($phone, $id)) $errors[] = "phone-taken";
    return $errors;
}

function updateProfileDetails($id, $email, $username, $phone, $fname, $lname, $password): void
{
    $query = "SELECT email FROM users WHERE id = :userid";
    $oldEmail = returnQuery($query, [":userid" => $id])["email"];
    if ($oldEmail != $email) sendEmailAddressChange($email, $username, $id);

    $query = "UPDATE users SET firstname = :fname, lastname = :lname, username = :username, phone = :phone WHERE id = :userid";
    $params = ["fname" => $fname, "lname" => $lname, "username" => $username, "phone" => $phone, "userid" => $id];
    doQuery($query, $params);

    if ($password != "") {
        $query = "UPDATE users SET pwd = :pwd WHERE id = :userid";
        $params = ["pwd" => hashPassword($password)];
        doQuery($query, $params);
    }
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

function handlePictureFromBase64($base64String, $id): int
{
    $allowedTypes = ["image/jpeg", "image/png"];
    $maxFileSize = 50 * 1024 * 1024; // 50MB max file size

    // Decode the Base64 string and generate a temporary file
    $decodedImage = base64_decode($base64String);
    if ($decodedImage === false) {
        return 2; // Invalid Base64 string
    }

    // Create a temporary file path
    $tempFilePath = tempnam(sys_get_temp_dir(), 'pfp_');
    file_put_contents($tempFilePath, $decodedImage);

    // Detect the image type
    $fileInfo = getimagesize($tempFilePath);
    if ($fileInfo === false || !in_array($fileInfo['mime'], $allowedTypes)) {
        unlink($tempFilePath); // Remove the temporary file
        return 2; // Unsupported file type
    }

    // Validate file size
    $fileSize = strlen($decodedImage);
    if ($fileSize > $maxFileSize) {
        unlink($tempFilePath);
        return 11; // File size exceeds limit
    }

    // Generate a new unique file name (to avoid overwriting)
    $fileExtension = pathinfo($fileInfo['mime'], PATHINFO_EXTENSION);
    $newFileName = $id . "." . $fileExtension;
    $uploadDir = "../pfp/";
    $uploadFilePath = $uploadDir . $newFileName;

    // Remove old files
    $extensions = ["jpeg", "jpg", "png"];
    foreach ($extensions as $ext) {
        $oldFile = $uploadDir . $id . "." . $ext;
        if (file_exists($oldFile)) unlink($oldFile);
    }

    // Move the temporary file to the upload directory
    if (rename($tempFilePath, $uploadFilePath)) {
        cropImageToSquare($uploadFilePath);
        resizeImage($uploadFilePath, 150);
        return 0; // Success
    } else {
        unlink($tempFilePath); // Cleanup on failure
        return 11; // File upload error
    }
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
        // Error: Only JPG and PNG files are allowed.;
        return 2;
    } else if ($fileSize > $maxFileSize) {
        // Error: File size exceeds the 50MB limit.;
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
            // File successfully uploaded to: $uploadFilePath;
            return 0;
        } else {
            // Error: There was a problem uploading the file.;
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