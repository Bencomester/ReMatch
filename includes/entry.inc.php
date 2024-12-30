<?php
require "dhb.inc.php";
require_once "config.php";

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
    ;";

    return returnQueryAll($query, [
        ":search" => "%$search%", // Include '%' for partial matching
        ":userLat" => $userLat, // User's latitude
        ":userLon" => $userLon, // User's longitude
    ]);
}

function getMyEntries(int $userid): array
{
    $query = "
        SELECT 
            username, 
            SUM(number) AS amount, 
            SUM(price * number) AS price,
            timestamp AS time
        FROM 
            users, blog, blogs 
        WHERE 
            blog.blog_id = blogs.blog_id 
            AND userid = users.id
            AND userid = :userid
        GROUP BY 
            blog.blog_id
        ORDER BY 
            'time' DESC
    ;";

    return returnQueryAll($query, [":userid" => $userid]);
}

function getEntryDetails(int $id): array
{
    $query = "
        SELECT 
            price, item, number AS amount
        FROM 
            users, blog, blogs 
        WHERE 
            blog.blog_id = blogs.blog_id 
            AND userid = users.id
            AND blogs.blog_id = :id
        ORDER BY
            item DESC,
            amount DESC,
            price DESC
    ;";

    return returnQueryAll($query, [":id" => $id]);
}

function editEntry(int $id, int $userid, array $items, int $lon = 0, int $lat = 0): bool
{
    $query = "SELECT userid FROM blog WHERE blog_id = :id;";
    if (returnQuery($query, ["id" => $id])["userid"] != $userid) return false;

    doQuery("DELETE FROM blogs WHERE blog_id = :id", ["id" => $id]);

    for ($i = 0; $i < count($items); $i++) {
        $query = "INSERT INTO blogs (item, number, price, blog_id) values (:items, :number, :price, :blog_id);";
        $param = [":items" => $items[$i]["item"], ":number" => $items[$i]["amount"], ":price" => $items[$i]["price"], ":blog_id" => $id];
        doQuery($query, $param);
    }

    if ($lon == 0 && $lat == 0) return true;
    $query = "UPDATE blog SET lon = :lon, lat = :lat WHERE blog_id = :id;";
    $param = [":id" => $id, ":lon" => $lon, ":lat" => $lat];
    doQuery($query, $param);
    return true;
}

function deleteEntry(int $id, int $userid): bool
{
    $query = "SELECT userid FROM blog WHERE blog_id = :id;";
    if (returnQuery($query, ["id" => $id])["userid"] != $userid) return false;
    doQuery("DELETE FROM blogs WHERE blog_id = :id", ["id" => $id]);
    doQuery("DELETE FROM blog WHERE blog_id = :id", ["id" => $id]);
    return true;
}