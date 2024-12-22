<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GeoIp2\Database\Reader;

function getLocationData($ip) {
    try {
        // Path to the GeoLite2 database file
        $databaseFile = __DIR__ . '/GeoLite2-City.mmdb';

        // Initialize the GeoIP2 Reader
        $reader = new Reader($databaseFile);

        // Replace with the user's IP address
        $ipAddress = '[REDACTED_IP]';

        // Query the database
        $record = $reader->city($ipAddress);

        // Extract location details
        $country = $record->country->name;    // Country name
        $city = $record->city->name;          // City name
        $latitude = $record->location->latitude;
        $longitude = $record->location->longitude;

        return ["country" => $country, "city" => $city, "lng" => $longitude, "lat" => $latitude];

    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // Check for shared internet IP
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Check for the forwarded IP address
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        // Default to remote address
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) : float {
    $earthRadius = 6371; // Radius in kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}


