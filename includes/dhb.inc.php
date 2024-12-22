<?php
/** @noinspection ALL */
header('Content-Type: text/html; charset=utf-8');
$dsn="mysql:host=localhost;dbname=rematch";
$dbusername ="root";
$dbpassword="[REDACTED_DB_PASSWORD]";
try{
    $pdo=new PDO($dsn,$dbusername,$dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

function doQuery($query, $params) : void {
    global $pdo;
    try{
        $stmt=$pdo->prepare($query);
        foreach($params as $key=>$value) {
            $stmt->bindValue($key,$value);
        }
        $stmt->execute();
    } catch(PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

function returnQuery($query, $params = []) {
    global $pdo;
    try{
        $stmt=$pdo->prepare($query);
        foreach($params as $key=>$value) {
            $stmt->bindValue($key,$value);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

function returnQueryAll($query, $params = [], $mode = PDO::FETCH_ASSOC) {
    global $pdo;
    try{
        $stmt=$pdo->prepare($query);
        foreach($params as $key=>$value) {
            $stmt->bindValue($key,$value);
        }
        $stmt->execute();
        return $stmt->fetchAll($mode);
    } catch(PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}
