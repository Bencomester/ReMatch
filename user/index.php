<?php
require_once '../includes/config.php';
require_once '../includes/dhb.inc.php';
require_once "../lang/index.php";
require_once '../myprofile/userdata.php';
require_once "../includes/navbar.php";
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

$texts = initLang();
?>

<!DOCTYPE html>
<html lang=<?= $texts["lang"] ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= str_replace("{username}", $data["username"], $texts["title-user"]) ?></title>
    <link rel="stylesheet" href="../myprofile/styles.css">
</head>
<body>
<?= navbar() ?>
<div class="profile-container">
    <?php
    if ($texts["lang"] == "hu") $name = $data["lastname"] . " " . $data["firstname"];
    else $name = $data["firstname"] . " " . $data["lastname"];
    if (isset($data["firstname"]) || $data["lastname"]) echo '<h2 class="user-name">' . $name . '</h2>'
    ?>

    <div class="profile-picture">
        <img src="<?= getpfp($id) ?>" alt="Profile Picture">
    </div>

    <h2 class="user-name"><?= $data["username"] ?></h2>
    <p class="user-email"><?= $data["email"] ?></p>
    <?= empty($data["phone"]) ? "" : '<p class="user-email">' . $data["phone"] . '</p>' ?>

    <div class="profile-actions">
        <button class="btn edit-btn" onclick="window.location.href='../myprofile';"><?= $texts["my-profile"] ?></button>
    </div>
</body>
</html>

