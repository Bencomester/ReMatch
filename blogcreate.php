<?php
require_once 'includes/config.php';
// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['logged-in'])) {
    header("Location: .");
    exit();
}
require_once "includes/geoip.php";
$data = getLocationData(getUserIP())
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles3.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=[REDACTED_MAPS_API_KEY]&libraries=places" async></script>
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
    <div class="container">
        <form action="includes/blogcreate1.inc.php" method="post">
            <input type="hidden" name="long" id="long" value="<?= $data["lng"] ?? 19.045900?>">
            <input type="hidden" name="lat" id="lat" value="<?= $data["lat"] ?? 47.507690?>">
            <div class="item" id="output"></div>
            <div class="button_div"><button type="button" onclick="generateCode()" class="newitem">New Item</button></div>
            <div id="map"></div>
            <div class="button_div"><button type="submit" class="sumbit" >Submit</button></div>
        </form>
    </div>

    <script>
        async function initMap() {
            const longInp = document.getElementById("long")
            const latInp = document.getElementById("lat")
            const initialLocation = {lat: parseFloat(latInp.value), lng: parseFloat(longInp.value)};
            const {AdvancedMarkerElement} = await google.maps.importLibrary("marker")

            // Initialize the map
            const map = new google.maps.Map(document.getElementById("map"), {
                mapId: "1",
                zoom: 12,
                center: initialLocation,
            });

            // Add a marker
            let marker = new AdvancedMarkerElement({
                position: initialLocation,
                map: map,
                title: "Initial Location",
            });

            // Add a click listener to add markers
            map.addListener('click', function (event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();

                // Remove the old marker
                if (marker) {
                    marker.map = null; // This removes the marker from the map
                }

                // Create a new marker
                marker = new AdvancedMarkerElement({
                    position: {lat, lng},
                    map: map,
                    title: "New Marker",
                });


                longInp.value = lng
                latInp.value = lat
            });
        }

        window.onload = initMap;
    </script>
    <script>
        function generateCode() {
            const output = document.getElementById('output');

            // Create the new elements
            const div = document.createElement('div')
            div.class = "item_sor";
            const h1 = document.createElement('h1');
            const select = document.createElement('select');
            select.name = "item[]";
            select.innerHTML = `
                <option value="item"> ITEM </option>
                <option value="1"> PET PALACK (50 ft) </option>
                <option value="2">ÜVEG (50 ft)</option>
            `;

            const counterInput = document.createElement('input');
            counterInput.type = 'number';
            counterInput.id = 'counter';
            counterInput.name='number[]';
            counterInput.value = 0;
            counterInput.readOnly = true;

            const incrementButton = document.createElement('button');
            incrementButton.textContent = '+';
            incrementButton.type='button';
            incrementButton.onclick = function() {
                counterInput.value = parseInt(counterInput.value) + 1;
            };

            const decrementButton = document.createElement('button');
            decrementButton.textContent = '-';
            decrementButton.type='button';
            decrementButton.onclick = function() {
                counterInput.value = parseInt(counterInput.value)===0 ? parseInt(counterInput.value):parseInt(counterInput.value)-1;
            };

            const img = document.createElement('img');
            img.src = 'images/bin1.png';
            img.alt = 'Delete';
            img.height=20;
            img.width=20;


            const deleteButton = document.createElement('button');
            deleteButton.textContent =  '';
            deleteButton.type='button';
            deleteButton.onclick = function() {
                deleteButton.parentElement.remove();
            };


            // Append elements to the output container
            output.appendChild(div)
            div.appendChild(h1);
            div.appendChild(select);
            div.appendChild(counterInput);
            div.appendChild(incrementButton);
            div.appendChild(decrementButton);
            div.appendChild(deleteButton);
            deleteButton.appendChild(img);
        }
        generateCode();
    </script>
</body>
</html>
