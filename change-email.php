<?php
require_once "includes/dhb.inc.php";
require_once "includes/config.php";
$success = false;
$email = "";
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET["email"];
    $token = $_GET["token"];
    $param = ["email" => $email, "token" => $token, "date" => date("U")];
    $data = returnQuery("SELECT * FROM email_verifications WHERE email = :email AND token = :token AND expiry > :date", $param);
    if (!empty($data)) {
        $data = returnQuery("SELECT * FROM users WHERE id = :userid AND verified", ["userid" => $data["userid"]]);
        if (!empty($data)) {
            $success = true;
            doQuery("UPDATE users SET email = :email WHERE id = :userid", ["userid" => $data["id"], "email" => $email]);
            doquery("DELETE FROM email_verifications WHERE email = :email AND token = :token", ["email" => $email, "token" => $token]);
        }
    }
}

if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "lang/$lang.php";
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title><?= $texts["title-verification"] ?></title>
</head>
<body>
<div class="login-container">
    <?php
    if ($success) {
        $h1 = $texts["email-update-success"];
        $p1 = $texts["email-updated"];
        $a = $texts["go-to-profile"];
        echo <<<EOD
        <h1>$h1</h1>
    <p class="login-success">$p1</p>
    <a href="http://server/rematch/myprofile">$a</a>
    EOD;} else {
        $h1 = $texts["email-update-failure"];
        $p1 = $texts["email-update-invalid"];
        $a = $texts["request-new-email-update"];
        echo <<<EOD
    <h1>$h1</h1>
    <p class="login-error">$p1</p>
    <a href="http://server/rematch/myprofile/edit.php">$a</a>
    EOD;}
    ?>
</div>
</body>
</html>


