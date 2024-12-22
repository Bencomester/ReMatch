<?php
require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/security.php";
require_once __DIR__ . "/includes/2fa.inc.php";
include(__DIR__ . "/lang/index.php");

if (!isset($_SESSION['userid'])) {
    header("Location: .");
    exit();
}

if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include __DIR__ . "/lang/$lang.php";

$enabled = is2faOnAndEnabled();
if (empty($enabled)) {
    $code = create2fa();
    $enabled = 0;
} else $enabled = $enabled["enabled"];
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles.css">
    <title>2tfa</title>
</head>
<body>
<?= languageDropdown($texts["choose-language"], $_SESSION['lang'] ?? "en") ?>
<div class="login-container">
    <h2><?= $enabled == 0 ? "Enable 2fa" : "Verify 2fa"?></h2>
    <?php
    if (isset($_GET["enable"])) {
        $class = $_GET["enable"] == "success" ? "login-success" : "login-error";
        $message = $_GET["enable"] == "success" ? "2fa successfully enabled!" : "Invalid code!";
        echo "<div class=$class>$message</div>";
    }
    ?>
    <?php if (isset($code)) echo '<img src="data:image/png;base64,' . $code . '" alt="QR Code">'?>
    <form action="includes/2fa.inc.php" id="form" method="post">
        <label for="otp">2fa code</label>
        <input
            type="text"
            name="otp"
            id="otp"
            placeholder="123 456"
            autocomplete="off" required>
        <button type="submit" class="submit" id="submit" data-loading-text="Loading...">
            <span class="button-text"><?= $texts["login"]?></span>
            <span class="spinner"></span>
        </button>

    </form>
    <a href="myprofile">My profile</a>
</div>

</body>
</html>

