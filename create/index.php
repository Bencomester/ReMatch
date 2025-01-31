<?php
require_once '../includes/config.php';
require_once "../lang/index.php";
require_once "../includes/navbar.php";
require_once "../includes/geoip.php";

// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['logged-in'])) {
    header("Location: .");
    exit();
}


$texts = initLang();
$data = getLocationData(getUserIP())
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../styles/styles_create.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/languagedropdown.css">
    <style>
        #map {
            height: 130%;
            width: 95%;
            align-content: center;
            align-self: center;
            margin: 10px;
        }
    </style>
    <title>Blog create</title>
</head>
<body>
<?= navbar() ?>
<div class="container">
    <form id="form" action="create.php" method="post">
        <input type="hidden" name="long" id="long" value="<?= $data["lng"] ?? 19.045900 ?>">
        <input type="hidden" name="lat" id="lat" value="<?= $data["lat"] ?? 47.507690 ?>">
        <div class="item" id="output">
            <div class="item_sor">
                <label for="plastic"><?= $texts["plastic-bottle"] ?></label>
                <input id="plastic" type="number" name="plastic" min="0" step="1" value="1">
            </div>
            <div class="item_sor">
                <label for="glass"><?= $texts["glass-bottle"] ?></label>
                <input id="glass" type="number" name="glass" min="0" step="1" value="1">
            </div>
            <div class="item_sor">
                <label for="aluminium"><?= $texts["aluminium"] ?></label>
                <input id="aluminium" type="number" name="aluminium" min="0" step="1" value="1">
            </div>
        </div>
        <div id="map"></div>
        <div class="button_div">
            <button type="submit" class="submit">Submit</button>
        </div>
    </form>
</div>

<!--suppress JSValidateTypes -->
<script>
    let map, marker;
    const longInp = document.getElementById("long");
    const latInp = document.getElementById("lat");

    async function loadGoogleMaps() {
        return new Promise((resolve, reject) => {
            if (window.google && google.maps) {
                resolve(); // Google Maps is already loaded
                return;
            }

            const script = document.createElement("script");
            script.src = "https://maps.googleapis.com/maps/api/js?key=[REDACTED_MAPS_API_KEY]&libraries=marker,places&callback=initMap";
            script.async = true;
            script.defer = true;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async function initMap() {
        await loadGoogleMaps();

        const initialLocation = {
            lat: parseFloat(latInp.value),
            lng: parseFloat(longInp.value)
        };

        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

        // Initialize the map
        map = new google.maps.Map(document.getElementById("map"), {
            mapId: "1",
            zoom: 12,
            center: initialLocation,
        });

        getLocation(AdvancedMarkerElement)

        // Add a marker
        createNewMarker(AdvancedMarkerElement, initialLocation.lat, initialLocation.lng);

        // Add a click listener to add markers
        map.addListener("click", (event) => {
            createNewMarker(AdvancedMarkerElement, event.latLng.lat(), event.latLng.lng());
        });
    }

    function getLocation(AdvancedMarkerElement) {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latInp.value = position.coords.latitude;
                    longInp.value = position.coords.longitude;
                    createNewMarker(AdvancedMarkerElement, position.coords.latitude, position.coords.longitude)
                },
                (error) => {
                    console.warn("Geolocation error:", error);
                }
            );
        }
    }

    function createNewMarker(AdvancedMarkerElement, lat, lng) {
        // Remove the old marker
        if (marker) marker.setMap(null); // Remove old marker

        // Create a new marker
        marker = new AdvancedMarkerElement({
            position: {lat, lng},
            map: map,
            title: "Your Location",
        });

        // Update hidden input values
        longInp.value = lng;
        latInp.value = lat;
    }

    // Form validation
    const form = document.getElementById("form");
    form.addEventListener("submit", function (event) {
        const items = document.querySelectorAll("select[name='item[]']");
        const numbers = document.querySelectorAll("input[name='number[]']");
        const prices = document.querySelectorAll("input[name='price[]']");

        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const number = numbers[i];
            const price = prices[i];

            if (item.value === "") {
                // Maybe some css highlighting here as well (eg: item.class = ...)
                alert("Please choose an item for all rows.");
                event.preventDefault();
                return;
            }

            if (parseInt(number.value) < 1) {
                // Maybe some css highlighting here as well (eg: number.class = ...)
                alert("Please enter a valid number of items (at least 1).");
                event.preventDefault();
                return;
            }

            if (parseFloat(price.value) < 5 || parseFloat(price.value) > 300) {
                // Maybe some css highlighting here as well (eg: price.class = ...)
                alert("Please enter a valid price (greater than 0 and less than 300).");
                event.preventDefault();
                return;
            }
        }
    });

    window.onload = initMap();
</script>
</body>
</html>
