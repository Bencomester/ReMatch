<?php
require "dhb.inc.php";

function createEntry(int $userid, int $plastic, int $glass, int $alu, float $lon, float $lat): void
{
    $total = $plastic + $glass + $alu;
    $worth = $total * 50;
    $price = $total * 24;
    $query = "INSERT INTO entry (userid, plastic, glass, aluminium, worth, price, total, lon, lat, timestamp) values (:userid, :plastic, :glass, :alu, :worth, :price, :total, :lon, :lat, unix_timestamp());";
    $param = ["userid" => $userid, "plastic" => $plastic, "glass" => $glass, "alu" => $alu, "lon" => $lon, "lat" => $lat, "total" => $total, "worth" => $worth, "price" => $price];
    doQuery($query, $param);
}

function getEntriesSearchAndSort($search = "", $sort = "time", bool $descending = false, int $limit = 50, float $userLat = 0, float $userLon = 0): array
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
            entry.id AS id,
            username, 
            plastic,
            glass,
            aluminium,
            total AS amount,
            price,
            worth,
            timestamp AS time,
            (6371 * acos(
                cos(radians(:userLat)) * 
                cos(radians(lat)) * 
                cos(radians(lon) - radians(:userLon)) + 
                sin(radians(:userLat)) * 
                sin(radians(lat))
            )) AS distance
        FROM 
            users, entry 
        WHERE
            userid = users.id 
            AND username LIKE :search 
        ORDER BY 
            $sort $order
        LIMIT $limit
    ;";

    return returnQueryAll($query, [
        ":search" => "%$search%", // Include '%' for partial matching
        ":userLat" => $userLat, // User's latitude
        ":userLon" => $userLon, // User's longitude
    ]);
}

function getEntryById(int $id, float $lat = 0, float $lon = 0) {
    $query = "
        SELECT
            username, 
            plastic,
            glass,
            aluminium,
            total AS amount,
            price,
            worth,
            timestamp AS time,
            (6371 * acos(
                cos(radians(:userLat)) * 
                cos(radians(lat)) * 
                cos(radians(lon) - radians(:userLon)) + 
                sin(radians(:userLat)) * 
                sin(radians(lat))
            )) AS distance
        FROM 
            users, entry 
        WHERE
            userid = users.id 
            AND entry.id = :id
    ;";
    return returnQuery($query, ["id" => $id, "userLat" => $lat, "userLon" => $lon]);
}

function getMyEntries(int $userid): array
{
    $query = "
        SELECT
            entry.id AS id,
            plastic,
            glass,
            aluminium,
            total AS amount,
            price,
            worth,
            timestamp AS time,
            lon,
            lat
        FROM 
            users, entry 
        WHERE
            userid = users.id
            AND userid = :userid
        ORDER BY 
            'time' DESC
    ;";

    return returnQueryAll($query, [":userid" => $userid]);
}

function editEntry(int $id, int $userid, int $plastic, int $glass, int $alu, float $lon = 0, float $lat = 0): bool
{
    $query = "SELECT userid, lon, lat FROM entry WHERE id = :id;";
    $result =  returnQuery($query, ["id" => $id]);
    if ($result["userid"] != $userid) return false;
    $total = $plastic + $glass + $alu;
    $worth = $total * 50;
    $price = $total * 24;
    if ($lon == 0) $lon = $result["lon"];
    if ($lat == 0) $lat = $result["lat"];
    $query = "UPDATE entry SET plastic = :plastic, glass = :glass, aluminium = :alu, lon = :lon, lat = :lat, total = :total, worth = :worth, price = :price WHERE id = :id;";
    $param = ["userid" => $userid, "plastic" => $plastic, "glass" => $glass, "alu" => $alu, "lon" => $lon, "lat" => $lat, "id" => $id, "total" => $total, "worth" => $worth, "price" => $price];
    doQuery($query, $param);
    return true;
}

function deleteEntry(int $id, int $userid): bool
{
    $query = "SELECT userid FROM entry WHERE id = :id;";
    $result = returnQuery($query, ["id" => $id]);
    if (!$result || !isset($result["userid"]) || $result["userid"] != $userid) return false;
    doQuery("DELETE FROM entry WHERE id = :id", ["id" => $id]);
    return true;
}