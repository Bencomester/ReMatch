<?php
require_once "includes/config.php";
require '../includes/dhb.inc.php';
include_once '../lang/index.php';
$token = $_GET['token'] ?? '';
$query = "SELECT email FROM password_resets WHERE token = :token AND expiry >= :now";
$values = ["token" => $token, "now" => date('U')];
$data = returnQuery($query, $values);

if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "../lang/$lang.php";
$error = $_SESSION['reset-error'];
if (empty($data) && !isset($_SESSION['reset_success'])) $error = "invalid-or-expired-token";
?>

<!DOCTYPE html>
<html lang="<?=$_SESSION["lang"] ?? "en"?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../styles.css">
    <title><?=$texts["reset-password"]?></h2></title>
</head>
<body>
<?= languageDropdown($texts["choose-language"], $_SESSION['lang'] ?? "en") ?>
<div class="login-container">
<h2><?=$texts["reset-password"]?></h2>
    <?php
    if (isset($error)) {
        echo '<div class="login-error">' . $texts[$error] . '</div>';
        unset($_SESSION['reset-error']);
    } else if (isset($_SESSION['reset-success'])) {
        echo '<div class="login-success">' . $texts[$_SESSION['reset_success']] . '</div>';
        unset($_SESSION['reset_success']);
    }
    ?>
<form action="tryReset.php" method="POST">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <label for="password"><?=$texts["new-password"]?></h2></label>
    <input type="password" name="password" id="password" placeholder="<?=$texts["enter-new-password"]?>" required>
    <label for="confirm_password"><?=$texts["confirm-new-password"]?></h2></label>
    <input type="password" name="confirm_password" id="confirm_password" placeholder="<?=$texts["confirm-your-new-password"]?>" required>
    <button type="submit" class="submit"><?=$texts["reset-password"]?></button>
</form>
</div>
</body>
</html>
