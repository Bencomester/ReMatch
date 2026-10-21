<?php
require_once __DIR__ . "/../includes/geoip.php";
function loginEmail($username, $time) : string {
    $text = include "../lang/en.php";
    $reset = "http://server/rematch/password/";
    $support = "https://yourwebsite.com/contact";
    $year = date("Y");
    $ip = getUserIP();
    $ip_details = getLocationData($ip);
    $location = empty($ip_details['country']) ? $text["unknown"] : $ip_details['country'] . ", " . $ip_details['city'];
    return <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Successful Login Notification</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f7; color: #333;">

<table width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f7; padding: 20px;">
    <tr>
        <td align="center">
            <table width="100%" max-width="600px" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
                
                <tr>
                    <td style="background-color: #4CAF50; padding: 20px; text-align: center;">
                        <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Successful Login</h1>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 30px 20px; text-align: left;">
                        <p style="font-size: 16px; color: #555;">Hello $username,</p>
                        <p style="font-size: 16px; color: #555;">We just wanted to let you know that a successful login to your account was detected:</p>
                        
                        <ul style="font-size: 14px; color: #555; list-style-type: none; padding: 0;">
                            <li><strong>Date & Time:</strong> $time</li>
                            <li><strong>IP Address:</strong> $ip</li>
                            <li><strong>Location:</strong> $location</li>
                        </ul>
                        
                        <p style="font-size: 14px; color: #555;">If this was you, no further action is needed. If you didn't sign in, please <a href=$reset style="color: #4CAF50; text-decoration: none;">reset your password</a> immediately and secure your account.</p>
                        
                        <p style="font-size: 16px; color: #555;">Best regards,<br>Your Company Team</p>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #f4f4f7; padding: 20px; text-align: center; font-size: 12px; color: #888;">
                        <p style="margin: 0;">For any questions or concerns, please <a href=$support style="color: #4CAF50; text-decoration: none;">contact support</a>.</p>
                        <p style="margin-top: 10px;">© $year Rematch, All Rights Reserved</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
EOD;}
