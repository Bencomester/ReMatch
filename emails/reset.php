<?php
function resetEmail($resetLink, $minute) : string {
    $year = date("Y");
    return <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f7; color: #333;">

<table width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f7; padding: 20px;">
    <tr>
        <td align="center">
            <table width="100%" max-width="600px" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden;">

                <tr>
                    <td style="background-color: #ff6f20; padding: 20px; text-align: center;">
                        <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Password Reset Request</h1>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 40px 20px; text-align: left;">
                        <p style="font-size: 16px; color: #555;">Hello,</p>
                        <p style="font-size: 16px; color: #555;">You recently requested to reset your password. Click the button below to set up a new password (link expires in $minute minutes):</p>

                        <p style="text-align: center; margin: 30px 0;">
                            <a href="$resetLink" style="background-color: #ff6f20; color: #ffffff; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;">
    Reset Password
</a>
                        </p>

                        <p style="font-size: 14px; color: #555;">If you didn’t request this, you can safely ignore this email. Your password won’t change until you create a new password.</p>

                        <p style="font-size: 16px; color: #555;">Best regards,<br>Your Company Team</p>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #f4f4f7; padding: 20px; text-align: center; font-size: 12px; color: #888;">
                        <p style="margin: 0;">If you’re having trouble clicking the password reset button, copy and paste the URL below into your web browser:</p>
                        <p style="color: #ff6f20;">$resetLink</p>
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
