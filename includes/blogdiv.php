<?php
require "dhb.inc.php";

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
