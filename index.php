<?php
require_once "includes/config.php";
require_once "includes/entry.inc.php";
require_once "lang/index.php";
require_once "includes/navbar.php";

if (!isset($_SESSION['logged-in'])) {
    header("Location: login");
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

    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/navbar.css">
    <link rel="stylesheet" href="styles/blogs.css">
    <link rel="stylesheet" href="styles/circle.css">
    <link rel="stylesheet" href="styles/languagedropdown.css">
    <title>Rematch</title>
</head>
<body>
<?= navbar() ?>
<div id="blogspage">
    <?php

    foreach (getEntriesSearchAndSort() as $item) {
        ?>
        <div class="blog">
            User: <?= $item["username"] ?>
            <br>
            Amount: <?= $item["amount"] ?>
            <br>
            Total: <?= $item["price"] ?>Ft
            <br>
            Created: <?= date("Y-m-d", $item["time"]) ?>
            <br>
            Distance: <?= round($item["distance"], 1) // FIXME: Display only if we know the users location    ?>
            km
        </div>
        <?php
    }
    ?>
</div>

<a href="create/index.php" class="plus">
    <div class="circle">+</div>
</a>
<script>
    const hamburger = document.querySelector('.hamburger')
    const navlinks = document.querySelector('.nav-links')
    let menuOpen = false;

    hamburger.addEventListener('click', () => {
        if (menuOpen === false) {
            navlinks.style.display = "block";
            menuOpen = true;
        } else if (menuOpen === true) {
            navlinks.style.display = "none";
            menuOpen = false;
        }
    });

    function getLocation() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    let lat = position.coords.latitude;
                    let lon = position.coords.longitude;
                }
            );
        }
    }
</script>
</body>
</html>
