<?php
require "dhb.inc.php";
function getusername($blog_id):string{
    $query="SELECT username FROM users, blog WHERE blog_id=$blog_id AND userid=users.id;";
     $username=returnQuery($query)["username"];
    return $username;
}
function quantity($blog_id):int{
    $query="SELECT SUM(number) as quantity FROM blogs WHERE blog_id=$blog_id GROUP BY blog_id;";
    $quantity=returnQuery($query)["quantity"];
    return $quantity;
}
function price($blog_id):int{
    $query="SELECT SUM(price) as price FROM blogs WHERE blog_id=$blog_id GROUP BY blog_id;";
    $price=returnQuery($query)["price"];
    return $price;
}
function blogcount():int{
    $query="SELECT MAX(blog_id) as blogcount FROM blogs;";
    $blogcount=returnQuery($query)["blogcount"];
    return $blogcount ?? 0;
}