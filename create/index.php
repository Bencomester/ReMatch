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
    <link rel="stylesheet" href="../styles3.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/languagedropdown.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=[REDACTED_MAPS_API_KEY]&libraries=places&callback=initMap"
            async defer></script>
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
    <form id="form" action="../includes/entry.inc.php" method="post">
        <input type="hidden" name="long" id="long" value="<?= $data["lng"] ?? 19.045900 ?>">
        <input type="hidden" name="lat" id="lat" value="<?= $data["lat"] ?? 47.507690 ?>">
        <div class="item" id="output"></div>
        <div class="button_div">
            <button type="button" onclick="generateCode()" class="newitem">New Item</button>
        </div>
        <div id="map"></div>
        <div class="button_div">
            <button type="submit" class="sumbit">Submit</button>
        </div>
    </form>
</div>

<!--suppress JSValidateTypes -->
<script>
    let map, marker;
    const longInp = document.getElementById("long");
    const latInp = document.getElementById("lat");

    async function initMap() {
        const initialLocation = {
            lat: parseFloat(latInp.value),
            lng: parseFloat(longInp.value)
        };

        const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");

        // Initialize the map
        map = new google.maps.Map(document.getElementById("map"), {
            mapId: "1",
            zoom: 12,
            center: initialLocation,
        });

        getLocation()

        // Add a marker
        createNewMarker(AdvancedMarkerElement, initialLocation.lat, initialLocation.lng);

        // Add a click listener to add markers
        map.addListener("click", (event) => {
            createNewMarker(AdvancedMarkerElement, event.latLng.lat(), event.latLng.lng());
        });
    }

    function getLocation() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latInp.value = position.coords.latitude;
                    longInp.value = position.coords.longitude;
                }
            );
        }
    }

    function createNewMarker(AdvancedMarkerElement, lat, lng) {
        // Remove the old marker
        if (marker) {
            marker.map = null; // This removes the marker from the map
        }

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

    function generateCode() {
        const output = document.getElementById('output');

        // Create the new elements
        const div = document.createElement('div');
        div.className = "item_sor";

        const select = document.createElement('select');
        select.name = "item[]";
        select.innerHTML = `
            <option value=""> Choose an item </option>
            <option value="1"> PET PALACK </option>
            <option value="2"> ÜVEG </option>
            <option value="3"> ALUMÍNIUM </option>
        `;

        const priceInput = document.createElement("input");
        priceInput.type = "number";
        priceInput.name = "price[]";
        priceInput.value = 50; // Default value
        priceInput.min = 5;
        priceInput.max = 300;
        priceInput.step = 5;
        priceInput.readOnly = false;
        priceInput.addEventListener('keydown', function (event) {
            event.preventDefault()
        });

        const counterInput = document.createElement("input");
        counterInput.type = "number";
        counterInput.name = "number[]";
        counterInput.value = 1; // Default value
        counterInput.min = 1;
        counterInput.step = 1;
        counterInput.readOnly = false;
        counterInput.addEventListener('keydown', function (event) {
            event.preventDefault()
        });

        const incrementButton = document.createElement("button");
        incrementButton.textContent = "+";
        incrementButton.type = "button";
        incrementButton.onclick = function () {
            counterInput.value = parseInt(counterInput.value) + 1;
        };

        const decrementButton = document.createElement("button");
        decrementButton.textContent = "-";
        decrementButton.type = "button";
        decrementButton.onclick = function () {
            counterInput.value = Math.max(1, parseInt(counterInput.value) - 1);
        };

        const deleteButton = document.createElement("button");
        deleteButton.textContent = "Delete";
        deleteButton.type = "button";
        deleteButton.name = "delete"
        deleteButton.onclick = function () {
            if (document.querySelectorAll(".item_sor").length > 1) {
                output.removeChild(div); // Remove the current div
            } else {
                alert("You must keep at least one item.");
            }
        };

        // Append elements to the div
        div.appendChild(select);
        div.appendChild(priceInput);
        div.appendChild(counterInput);
        div.appendChild(incrementButton);
        div.appendChild(decrementButton);
        div.appendChild(deleteButton);

        // Append the div to the output container
        output.appendChild(div);
    }

    generateCode()

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
</script>
</body>
</html>
