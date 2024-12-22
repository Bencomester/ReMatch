<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require "dhb.inc.php";
function blogcreate($itemsjo, $numberjo,$id) {
    for ($i=0;$i<count($itemsjo);$i++) {
        $query = "INSERT INTO blogs (item, number, price, blog_id) values (:items, :number, :price, :blog_id);";
        $param = [":items" => $itemsjo[$i], ":number" => $numberjo[$i], ":price"=>getprice($itemsjo[$i],$numberjo[$i]), ":blog_id" => $id];
        doQuery($query, $param);
    }
    $query = "INSERT INTO blog (userid, blog_id) values (:userid, :blog_id);";
    $param = [":userid" => $_SESSION["userid"], ":blog_id" => $id];
    doQuery($query, $param);
}
function getprice($item,$number):int{
    $query="SELECT (items.price*$number) as price FROM items WHERE $item=items.id;";
    $price=returnQuery($query)["price"];
    return $price;
}
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
        ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}
function generateid():int{

    $query = "SELECT blog_id FROM blog;";
    $blog_ids=[];
    foreach (returnQueryAll($query) as $row){
        $blog_ids[]= $row["blog_id"];
        console_log($row["blog_id"]);
    }

    $id=1;
    while (true){
        if (!in_array($id,$blog_ids)){

            break;
        }
        if ($id>1000) break;
        $id++;
    }
    return $id;
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $items=$_POST["item"];
    $number = $_POST["number"];
    try {
        $itemsjo= array_filter($items, function ($value){return $value !=='';});
        $numberjo= array_filter($number, function ($value){return $value !=='';});
        if (count($itemsjo) !== count($numberjo)) {
            throw new Exception('The number of items does not match the number of numbers.');
        }
        blogcreate($itemsjo,$numberjo,generateid());
        header("Location: ../rematch.php?blogcreate=success");
        die();
    }
    catch(PDOException $e){
    die("Query failed: " . $e->getMessage());
}

}