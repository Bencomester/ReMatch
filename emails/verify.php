<?php
function verificationEmail($username, $link) : string {
    $year = date("Y");
    return <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f7; color: #333;">

<!-- Main Container -->
<table width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f7; padding: 20px;">
    <tr>
        <td align="center">
            <!-- Email Content Wrapper -->
            <table width="100%" max-width="600px" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
                
                <!-- Header -->
                <tr>
                    <td style="background-color: #4CAF50; padding: 20px; text-align: center;">
                        <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Verify Your Email Address</h1>
                    </td>
                </tr>
                
                <!-- Body -->
                <tr>
                    <td style="padding: 30px 20px; text-align: left;">
                        <p style="font-size: 16px; color: #555;">Hello $username,</p>
                        <p style="font-size: 16px; color: #555;">Thank you for signing up! Please confirm your email address by clicking the button below:</p>
                        
                        <p style="text-align: center; margin: 30px 0;">
                            <a href=$link style="background-color: #4CAF50; color: #ffffff; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;">
                                Verify Email
                            </a>
                        </p>
                        
                        <p style="font-size: 14px; color: #555;">If you didn’t create an account, you can safely ignore this email.</p>
                        
                        <p style="font-size: 16px; color: #555;">Best regards,<br>Your Company Team</p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color: #f4f4f7; padding: 20px; text-align: center; font-size: 12px; color: #888;">
                        <p style="margin: 0;">If you’re having trouble clicking the verification button, copy and paste the URL below into your web browser:</p>
                        <p style="color: #4CAF50;">$link</p>
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
