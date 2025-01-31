<?php
require_once "includes/config.php";
require_once "includes/entry.inc.php";

if (!isset($_SESSION['logged-in'])) {
    header("Location: login");
    exit();
}

header("Content-Type: application/json");

$sort = $_GET['sort'] ?? "time";
$order = $_GET['order'] ?? "DESC";
$search = $_GET['search'] ?? "";
$userLat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$userLon = isset($_GET['lon']) ? floatval($_GET['lon']) : 0;
$limit = $_GET['limit'] ?? 20;

// Fetch sorted entries
$entries = getEntriesSearchAndSort($search, $sort, $order === "DESC", $limit, $userLat, $userLon);

// Return JSON response
echo json_encode($entries);
