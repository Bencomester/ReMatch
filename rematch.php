<?php
require_once "includes/config.php";


// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['logged-in'])) {
    header("Location: .");
    exit();
}
require "includes/blogdiv.php";

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
<?php

            echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!";
            ?>
           <div id="blogspage">
               <?php

               for ($i = 1; $i <= blogcount(); $i++) {
                   ?>
                   <div class="blog">
                       User: <?= getusername($i); ?>
                       <br>
                       Amount: <?= quantity($i); ?>
                       <br>
                       Total: <?= price($i); ?>Ft
                   </div>
                   <?php
               }
               ?>
           </div>

    <div class="circle"><a href="blogcreate.php" class="plus">+</a></div>
    

</body>

</html>
