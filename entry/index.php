<?php
require_once "../includes/config.php";
require_once "../includes/entry.inc.php";
require_once "../lang/index.php";
require_once "../includes/navbar.php";

if (!isset($_SESSION['logged-in'])) {
    header("Location: login");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: ..");
    exit();
}

$entry = getEntryById($_GET["id"]);
if (empty($entry)) {
    header("Location: ..");
    exit();
}

$texts = initLang();
?>
<!DOCTYPE html>
<html lang=<?= $texts["lang"] ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="../styles/styles_entry.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/languagedropdown.css">
    <title>Rematch</title>
</head>
<body>
<?= navbar() ?>

<div class="blog">
    <?php foreach ($entry as $x => $y) echo "<p> $x: $y</p><br>";?>
</div>
</body>
</html>
