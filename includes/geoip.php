<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GeoIp2\Database\Reader;

function getLocationData($ipAddress)
{
    try {
        $databaseFile = __DIR__ . '/GeoLite2-City.mmdb';

        $reader = new Reader($databaseFile);

        $record = $reader->city($ipAddress);

        $country = $record->country->name;
        $city = $record->city->name;
        $latitude = $record->location->latitude;
        $longitude = $record->location->longitude;

        return ["country" => $country, "city" => $city, "lng" => $longitude, "lat" => $latitude];

    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// --Commented out by Inspection START (17/1/2025 12:14 PM):
//function calculateDistance($lat1, $lon1, $lat2, $lon2): float
//{  // Result in kilometers
//    $earthRadius = 6371; // Radius in kilometers
//
//    $dLat = deg2rad($lat2 - $lat1);
//    $dLon = deg2rad($lon2 - $lon1);
//
//    $a = sin($dLat / 2) * sin($dLat / 2) +
//        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
//        sin($dLon / 2) * sin($dLon / 2);
//
//    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
//
//    return $earthRadius * $c;
//}
// --Commented out by Inspection STOP (17/1/2025 12:14 PM)



