<?php
function emailChange($username, $link) : string {
    $support = "https://yourwebsite.com/contact";
    $year = date("Y");
    return <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Email Address Change</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #6880E9;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
        }
        .body {
            padding: 30px;
            color: #555;
        }
        .body p {
            line-height: 1.6;
        }
        .cta-button {
            display: inline-block;
            background-color: #5AB9EA;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #84CEEB;
        }
        .footer {
            background-color: #f4f4f7;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .footer a {
            color: #6880E9;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Confirm Your Email Address Change</h1>
        </div>

        <div class="body">
            <p>Hi $username,</p>
            <p>We received a request to change the email address associated with your account. If you made this request, please confirm your new email address by clicking the button below:</p>
            
            <a href=$link class="cta-button">Confirm Email Address</a>

            <p>If you did not request this change, please ignore this email. Your current email address will remain unchanged.</p>
            
            <p>If you have any questions, please <a href=$support>contact our support team</a>.</p>
            
            <p>Thank you for keeping your account secure!</p>
        </div>

        <div class="footer">
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p>$link</p>
            <p>© $year Your Company, All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>

EOD;}
