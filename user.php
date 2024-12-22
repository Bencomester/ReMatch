<?php
require_once('includes/config.php');
require_once('includes/dhb.inc.php');
require_once("lang/index.php");
require_once('myprofile/userdata.php');
if (!isset($_SESSION['logged-in'])) {
    header("Location: ..");
    exit();
}

$id = $_GET["userid"] ?? $_SESSION["userid"];
$data = getData($id);
if (empty($data) || $data["verified"] == 0) {
    $id = $_SESSION["userid"];
    $data = getData($id);
}


if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "lang/$lang.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= str_replace("{username}", $data["username"], $texts["title-user"]) ?></title>
    <link rel="stylesheet" href="myprofile/styles.css">
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
        <img src="<?= getpfp($id) ?>" alt="Profile Picture">
    </div>

    <!-- User Info -->
    <h2 class="user-name"><?=$data["username"]?></h2>
    <p class="user-email"><?=$data["email"]?></p>
    <?= empty($data["phone"]) ? "" : '<p class="user-email">' . $data["phone"] . '</p>'?>

    <!-- Profile Actions -->
    <div class="profile-actions">
        <button class="btn edit-btn" onclick="window.location.href='./myprofile';"><?= $texts["my-profile"] ?></button>
</div>
</body>
</html>

