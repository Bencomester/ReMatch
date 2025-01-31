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

    <link rel="stylesheet" href="styles/styles_entry.css">
    <link rel="stylesheet" href="styles/navbar.css">
    <link rel="stylesheet" href="styles/languagedropdown.css">
    <title>Rematch</title>
</head>
<body>
<?= navbar() ?>

<!-- Sorting Controls -->
<div class="sort-container">
    <label for="search">Search:</label>
    <input type="text" id="search" name="search">

    <label for="sortBy">Sort By:</label>
    <select id="sortBy">
        <option value="time">Time</option>
        <option value="username">Username</option>
        <option value="amount">Amount</option>
        <option value="price">Price</option>
        <option value="distance">Distance</option>
    </select>

    <label for="order">Order:</label>
    <select id="order">
        <option value="DESC">Descending</option>
        <option value="ASC">Ascending</option>
    </select>

    <label for="limit">Limit:</label>
    <input id="limit" type="number" min=10 max=100 step=10 value=20>

    <button onclick="fetchEntries()">Search</button>
</div>

<div id="blogspage"></div>

<a href="create" class="plus">
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

    let userLat = 0, userLon = 0;

    // Fetch and display entries
    async function fetchEntries() {
        const sortBy = document.getElementById("sortBy").value;
        const order = document.getElementById("order").value;
        const search = document.getElementById("search").value;
        const limit = document.getElementById("limit").value;

        const response = await fetch(`fetch_entries.php?sort=${sortBy}&order=${order}&lat=${userLat}&lon=${userLon}&search=${search}&limit=${limit}`);
        const entries = await response.json();

        const blogsPage = document.getElementById("blogspage");
        blogsPage.innerHTML = ""; // Clear previous entries

        entries.forEach(item => {
            const distanceText = item.distance ? `${item.distance.toFixed(1)} km` : "N/A";

            blogsPage.innerHTML += `
                <div class="blog">
                    <p><strong>User:</strong> ${item.username}</p><br>
                    <p><strong>Total Amount:</strong> ${item.amount}</p><br>
                    <p><strong>Plastic:</strong> ${item.plastic}</p><br>
                    <p><strong>Glass:</strong> ${item.glass}</p><br>
                    <p><strong>Aluminium:</strong> ${item.aluminium}</p><br>
                    <p><strong>Total Worth:</strong> ${item.worth} Ft</p><br>
                    <p><strong>Total Price:</strong> ${item.price} Ft</p><br>
                    <p><strong>Created At:</strong> ${new Date(item.time * 1000).toISOString().split('T')[0]}</p><br>
                    <p><strong>Distance:</strong> ${distanceText}</p>
                </div>
            `;
        });
    }

    // Get user location
    function getLocation() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLat = position.coords.latitude;
                    userLon = position.coords.longitude;
                    fetchEntries(); // Fetch data after getting location
                },
                () => fetchEntries() // If location denied, still fetch data
            );
        } else {
            fetchEntries(); // Fetch data if geolocation is not available
        }
    }

    window.onload = getLocation;
</script>
</body>
</html>
