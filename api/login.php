<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . "/../includes/dhb.inc.php";
require_once __DIR__ . "/tokenHandler.php";
$en = include __DIR__ . "/../lang/en.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $data = json_decode(file_get_contents("php://input"), true);

    $username = strtolower($data["username"] ?? '');
    $pwd = $data["password"] ?? '';


    $result = usernameQuery($username);

    if(empty($result)){
        $result = emailQuery($username);
    }
    if(!empty($result) && password_verify($pwd, $result["pwd"])){
        if ($result["verified"] == 0){
            $code = "email-not-verified";
            echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
            exit();
        }

        $tfa = is2faOnOrEnabled($result["id"]) == 1;
        if ($tfa && !verify2fa($data["2fa"] ?? 0, $result["id"])) {
            $code = "missing-or-wrong-2fa";
            echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
            exit();
        }

        date_default_timezone_set("UTC");
        $query="INSERT INTO login (userid, date) values (:id, :date);";
        $param = [":id" => $result["id"], ":date" => date("Y-m-d H:i:s")];
        doQuery($query, $param);
        $code = "login-success";
        echo json_encode(["status" => "success", "code" => $code, "message" => $en[$code], "token" => generateJWT($result["id"])]);
    }else{
        $code = "login-error";
        echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
        exit();
    }
}
else{
    http_response_code(405); // Method Not Allowed
    $code = "invalid-request";
    echo json_encode(["status" => "error", "code" => $code, "message" => $en[$code]]);
}

function usernameQuery($username) {
    $query="SELECT pwd, id, verified FROM users WHERE username = :username";
    $param = [":username" => $username];
    return returnQuery($query, $param);
}

function emailQuery($email) {
    $query="SELECT pwd, id, verified FROM users WHERE email = :email";
    $param = [":email" => $email];
    return returnQuery($query, $param);
}