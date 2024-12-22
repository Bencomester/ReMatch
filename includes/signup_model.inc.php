<?php

declare(strict_types=1);
require_once "dhb.inc.php";

function get_username(string $username)
{
    $query="SELECT username, id FROM users WHERE LOWER(username) = :username;";
    return returnQuery($query,[":username"=> strtolower($username)]);
}

function get_email(string $email)
{
    $query="SELECT email, id FROM users WHERE email = :email;";
    return returnQuery($query,[":email"=>$email]);
}

function get_phone(string $phone)
{
    $query="SELECT phone, id FROM users WHERE phone = :phone;";
    return returnQuery($query,[":phone"=>$phone]);
}

function set_user(string $username, string $pwd, string $email, string $phone, string $firstname, string $lastname) : void{
    $query="INSERT INTO users (username, email, phone, pwd, firstname, lastname, pwdDate, verified) values (:username, :email, :phone, :pwd, :firstname, :lastname, :date, 0);";

    $options =[
        'cost' => 12
    ];

    $hashedPwd =password_hash($pwd, PASSWORD_BCRYPT, $options);
    date_default_timezone_set("UTC");
    $values = ["username"=>$username, "email"=>$email, "phone"=>$phone, "pwd"=>$hashedPwd, "firstname"=>$firstname, "lastname"=>$lastname, "date"=>date("Y-m-d H:i:s")];
    doQuery($query, $values);
}