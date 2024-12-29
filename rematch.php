<?php
require_once "includes/config.php";
require "includes/blogdiv.php";

if (!isset($_SESSION['logged-in'])) {
    header("Location: .");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles2.css">
    <title>Rematch</title>
</head>
<body>
<nav class="navbar">
    <div class="navdiv">
        <img class="logo" src="../favicon.ico" alt="logo">
        <h1>Rematch</h1>
        <button><a href="logout.php">Logout</a></button>
    </div>
</nav>
           <div id="blogspage">
               <?php

               foreach (getEntriesSearchAndSort() as $item) {
                   ?>
                   <div class="blog">
                       User: <?= $item["username"] ?>
                       <br>
                       Amount: <?= $item["amount"] ?>
                       <br>
                       Total: <?= $item["price"] ?>Ft
                       <br>
                       Created: <?= date("Y-m-d", $item["time"]) ?>
                   </div>
                   <?php
               }
               ?>
           </div>

    <div class="circle"><a href="blogcreate.php" class="plus">+</a></div>
</body>
</html>
