<?php
require_once '../includes/config.php';
require_once "../lang/index.php";
require_once "../includes/navbar.php";
require_once __DIR__ . "/../includes/2fa.inc.php";
require_once "userdata.php";

if (!isset($_SESSION['logged-in'])) {
    header("Location: ..");
    exit();
}

$texts = initLang();

$tfa = (is2faOnAndEnabled() ?? 0) == 1;
$data = getData($_SESSION['userid'])
?>

<!DOCTYPE html>
<html lang=<?= $texts["lang"] ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $texts["title-myprofile"] ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../styles/languagedropdown.css">
    <link rel="stylesheet" href="../styles/navbar.css">
</head>
<body>
<?= navbar() ?>
<!-- Profile Container -->
<div class="profile-container">
    <?php
    if ($texts["lang"] == "hu") $name = $data["lastname"] . " " . $data["firstname"];
    else $name = $data["firstname"] . " " . $data["lastname"];
    if (isset($data["firstname"]) || $data["lastname"]) echo '<h2 class="user-name">' . $name . '</h2>'
    ?>
    <!-- Profile Picture -->
    <div class="profile-picture">
        <img src="../<?= getpfp($_SESSION['userid']) ?>" alt="Profile Picture">
    </div>

    <!-- User Info -->
    <h2 class="user-name"><?= $data["username"] ?></h2>
    <p class="user-email"><?= $data["email"] ?></p>
    <?= empty($data["phone"]) ? "" : '<p class="user-email">' . $data["phone"] . '</p>' ?>

    <!-- Profile Actions -->
    <div class="profile-actions">
        <button class="btn edit-btn" onclick="window.location.href='edit.php';"><?= $texts["edit-profile"] ?></button>
        <button class="btn edit-btn"
                onclick="window.location.href='<?= $tfa ? "../2fa/?disable=true" : "../2fa" ?>';"><?= $tfa ? "Disable 2fa" : "Enable 2fa" ?></button>
        <button class="btn logout-btn" onclick="window.location.href='../logout.php';"><?= $texts["log-out"] ?></button>
    </div>
</div>
</body>
</html>
