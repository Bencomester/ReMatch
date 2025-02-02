<?php
require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/security.php";
require_once __DIR__ . "/../../includes/2fa.inc.php";
require_once __DIR__ . "/../../includes/navbar.php";

if (!isset($_SESSION['logged-in'])) {
    header("Location: ../../login");
    exit();
}

$texts = initLang();

$enabled = is2faOnOrEnabled($_SESSION["userid"]);
if ($enabled == 1) {
    header("Location: ../../myprofile");
    exit();
}
else if ($enabled == -1) $code = create2fa($_SESSION["userid"]);
else if ($enabled == 0) $code = getSecretQR($_SESSION["userid"]);
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../../styles.css">
    <link rel="stylesheet" href="../../styles/languagedropdown.css">
    <link rel="stylesheet" href="../../styles/navbar.css">
    <title>Rematch - Enable 2tfa</title>
</head>
<body>
<?= navbar() ?>
<div class="login-container">
    <h2>Enable 2fa</h2>
    <?php if ($_GET["verify"] ?? "" == "failed") echo "<div class='login-error'>Invalid code!</div>"; ?>
    <?php if (isset($code)) echo '<img src="data:image/png;base64,' . $code . '" alt="QR Code">' ?>
    <form action="enable.php" id="form" method="post">
        <label for="otp">2fa code</label>
        <input
            type="text"
            name="otp"
            id="otp"
            placeholder="123 456"
            autocomplete="off"
            autofocus required>
        <button type="submit" class="submit" id="submit" data-loading-text="Loading...">
            <span class="button-text"><?= $texts["verify-2fa"] ?></span>
            <span class="spinner"></span>
        </button>

    </form>
    <a href="../../myprofile">My profile</a>
</div>

<script>
    // Function to format the OTP field as 000 000
    function formatOtp(input) {
        // Remove all non-numeric characters
        let value = input.value.replace(/\D/g, '');

        // Limit to 6 digits
        if (value.length > 6) {
            value = value.substring(0, 6);
        }

        // Format value as 000 000 (inserting a space after every 3 digits)
        if (value.length <= 3) {
            input.value = value;
        } else {
            input.value = value.replace(/(\d{3})(\d{0,3})/, '$1 $2');
        }
    }

    // Add event listener for the OTP input field
    document.addEventListener("DOMContentLoaded", function () {
        const otpInput = document.getElementById('otp');

        otpInput.addEventListener('input', function () {
            formatOtp(otpInput);
        });
    });
</script>

</body>
</html>


