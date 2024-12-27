<?php
require "dhb.inc.php";
function getUsername($blog_id): string
{
    $query = "SELECT username FROM users, blog WHERE blog_id = $blog_id AND userid=users.id;";
    return returnQuery($query)["username"];
}

function quantity($blog_id): int
{
    $query = "SELECT SUM(number) as quantity FROM blogs WHERE blog_id = $blog_id GROUP BY blog_id;";
    return returnQuery($query)["quantity"];
}

function price($blog_id): int
{
    $query = "SELECT SUM(price * number) as price FROM blogs WHERE blog_id = $blog_id GROUP BY blog_id;";
    return returnQuery($query)["price"];
}

function entryCount(): int
{
    $query = "SELECT MAX(blog_id) as blogcount FROM blogs;";
    $blogcount = returnQuery($query)["blogcount"];
    return $blogcount ?? 0;
}