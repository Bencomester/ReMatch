<?php
require_once "includes/config.php";
require_once "includes/security.php";
include("lang/index.php");
require_once "includes/config.php";

if (isset($_SESSION['logged-in'])) {
    header("Location: rematch.php");
    exit();
}
$username = $_SESSION['username'] ?? null;
unset($_SESSION['username']);
unset($_SESSION["userid"]);

if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "lang/$lang.php";
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <title><?= $texts["title-login"]?></title>
</head>
<body>
<?= languageDropdown($texts["choose-language"], $_SESSION['lang'] ?? "en") ?>
<div class="login-container">
    <h2><?= $texts["login"]?></h2>
    <?php
    if (!empty($_SESSION["login-error"])) {
        echo '<div class="login-error">' . $texts["login-error"] . '</div>';
        unset($_SESSION["login-error"]);
    } else if (!empty($_SESSION["csrf-fail"])) {
        echo '<div class="login-error">' . $texts["session-expired"] . '</div>';
        unset($_SESSION["csrf-fail"]);
    }
   ?>
    <form action="includes/login.inc.php" id="form" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <label for="username"><?= $texts["email-or-username"]?></label>
        <input
                type="text"
                name="username"
                id="username"
                placeholder="<?= $texts["example-login"]?>"
                value="<?= isset($username) ? htmlspecialchars($username) : '';?>" required>
        <label for="password"><?= $texts["password"]?></label>
        <div class="password-container">
            <input
                type="password"
                name="password"
                id="password"
                placeholder="<?= $texts["enter-password"]?>" required>
            <button type="button" id="togglePassword" aria-label="Toggle password visibility">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <div class="cf-turnstile" data-sitekey="0x4AAAAAAAzcQ_AbxIt5mw3W" data-theme="light"></div>
        <button type="submit" class="submit" id="submit" data-loading-text="Loading...">
            <span class="button-text"><?= $texts["login"]?></span>
            <span class="spinner"></span>
        </button>

    </form>
    <a href="password"><?= $texts["forgot-password"]?></a>
    <a href="signup.php"><?= $texts["create-account"]?></a>
</div>
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("form");
        const submitButton = document.getElementById("submit");

        form.addEventListener("submit", function () {
            // Show loading state
            submitButton.disabled = true;
            submitButton.classList.add('loading');
        });
    });

</script>

</body>
</html>
