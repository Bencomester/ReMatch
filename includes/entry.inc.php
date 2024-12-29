<?php
require "dhb.inc.php";
require_once "config.php";

function createEntry($item, $price, $amount, $lon, $lat): void
{
    $id = generateId();
    for ($i = 0; $i < count($item); $i++) {
        $query = "INSERT INTO blogs (item, number, price, blog_id) values (:items, :number, :price, :blog_id);";
        $param = [":items" => $item[$i], ":number" => $amount[$i], ":price" => $price[$i], ":blog_id" => $id];
        doQuery($query, $param);
    }
    $query = "INSERT INTO blog (userid, blog_id, timestamp, lon, lat) values (:userid, :blog_id, unix_timestamp(), :lon, :lat);";
    $param = [":userid" => $_SESSION["userid"], ":blog_id" => $id, ":lon" => $lon, ":lat" => $lat];
    doQuery($query, $param);
}

function generateId(): int
{
    $query = "SELECT blog_id FROM blog;";
    $blog_ids = [];
    foreach (returnQueryAll($query) as $row) $blog_ids[] = $row["blog_id"];
    $id = 1;
    while (true) {
        if (!in_array($id, $blog_ids)) break;
        $id++;
    }
    return $id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $items = $_POST["item"];
    $number = $_POST["number"];
    $price = $_POST["price"];
    $lon = $_POST["long"];
    $lat = $_POST["lat"];
    createEntry($items, $price, $number, $lon, $lat);
    header("Location: ../rematch.php?create=success");
    exit();
}

function getEntriesSearchAndSort($search = "", $sort = "distance", bool $descending = false, int $limit = 50, float $userLat = 0, float $userLon = 0): array
{
    // Define allowed columns for sorting
    $allowedSortColumns = ["id", "username", "amount", "price", "time", "distance"];

    // Validate the sort parameter
    if (!in_array($sort, $allowedSortColumns, true)) {
        $sort = "id"; // Default to "id" if the sort column is invalid
    }
    if ($sort == "distance" && $userLat == 0 && $userLon == 0) $sort = "id";

    if ($sort == "id") $sort = "blog.blog_id";
    $order = $descending ? "DESC" : "ASC";

    $query = "
        SELECT 
            username, 
            SUM(number) AS amount, 
            SUM(price * number) AS price,
            timestamp AS time,
            (6371 * acos(
                cos(radians(:userLat)) * 
                cos(radians(lat)) * 
                cos(radians(lon) - radians(:userLon)) + 
                sin(radians(:userLat)) * 
                sin(radians(lat))
            )) AS distance
        FROM 
            users, blog, blogs 
        WHERE 
            blog.blog_id = blogs.blog_id 
            AND userid = users.id 
            AND username LIKE :search 
        GROUP BY 
            blog.blog_id
        ORDER BY 
            $sort $order
        LIMIT $limit
    ";

    return returnQueryAll($query, [
        ":search" => "%$search%", // Include '%' for partial matching
        ":userLat" => $userLat, // User's latitude
        ":userLon" => $userLon, // User's longitude
    ]);
}
