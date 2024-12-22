<?php
require_once '../includes/config.php';
// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['logged-in'])) {
    header("Location: ..");
    exit();
}
require_once("userdata.php");
include("../lang/index.php");
if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "../lang/$lang.php";

$data = getData($_SESSION['userid'])
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $texts["title-myprofile"] ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?= languageDropdown($texts["choose-language"], $_SESSION['lang'] ?? "en") ?>
<!-- Profile Container -->
<div class="profile-container">
    <?php
    if ($lang == "hu") $name = $data["lastname"] . " " . $data["firstname"];
    else $name = $data["firstname"] . " " . $data["lastname"];
    if (isset($data["firstname"]) || $data["lastname"]) echo '<h2 class="user-name">' . $name . '</h2>'
    ?>
    <!-- Profile Picture -->
    <div class="profile-picture">
        <img src="../<?= getpfp($_SESSION['userid']) ?>" alt="Profile Picture">
    </div>

    <!-- User Info -->
    <h2 class="user-name"><?=$data["username"]?></h2>
    <p class="user-email"><?=$data["email"]?></p>
    <?= empty($data["phone"]) ? "" : '<p class="user-email">' . $data["phone"] . '</p>'?>

    <!-- Profile Actions -->
    <div class="profile-actions">
        <button class="btn edit-btn" onclick="window.location.href='edit.php';"><?= $texts["edit-profile"] ?></button>
        <button class="btn edit-btn" onclick="window.location.href='../2fa.php';">Enable 2fa</button>
        <button class="btn logout-btn" onclick="window.location.href='../logout.php';"><?= $texts["log-out"] ?></button>
    </div>
</div>
</body>
</html>
