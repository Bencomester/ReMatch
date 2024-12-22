<?php
include "../lang/index.php";
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "../lang/$lang.php";
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION["lang"] ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../styles.css">
    <title><?= $texts["password-reset"] ?></title>
</head>
<body>
<?= languageDropdown($texts["choose-language"], $_SESSION['lang'] ?? "en") ?>
    <div class="login-container">
    <h2><?= $texts["reset-password"] ?></h2>
        <?php
        if(session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['reset'])) {
            echo '<div class="' . ($_SESSION['reset'] == 'reset-sent' ? 'login-error' : 'login-success') . '">' . $texts[$_SESSION["reset"]] . '</div>';
            unset($_SESSION['reset']);
        }
        ?>
        <form action="reset-password.php" method="POST">
            <label for="email"><?= $texts["email"] ?></label>
            <input type="email" name="email" id="email" placeholder="<?= $texts["enter-email"] ?>" required>
            <button type="submit" class="submit"><?= $texts["reset-my-password"] ?></button>
        </form>
        <a href=".."><?= $texts["i-know-my-password"] ?></a>
    </div>
</body>
</html>
