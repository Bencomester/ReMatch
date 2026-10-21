<?php
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/security.php";
require_once __DIR__ . "/../includes/2fa.inc.php";
include __DIR__ . "/../lang/index.php";

if (isset($_SESSION["logged-in"])) {
    header("Location: ../myprofile");
    exit();
}

if (!isset($_SESSION['userid'])) {
    header("Location: ../login");
    exit();
}

$texts = initLang();

$enabled = is2faOnOrEnabled($_SESSION["userid"]);

if ($enabled < 1) {
    header("Location: ../login");
    exit();
}

?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../styles.css">
    <title>Rematch - 2tfa</title>
</head>
<body>
<?= languageDropdown() ?>
<div class="login-container">
    <h2>Verify 2fa</h2>
    <?php if ($_GET["verify"] ?? "" == "failed") echo "<div class='login-error'>Invalid code!</div>"; ?>
    <form action="2fa.php" id="form" method="post">
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
</div>

<script>
    function formatOtp(input) {
        let value = input.value.replace(/\D/g, '');

        if (value.length > 6) {
            value = value.substring(0, 6);
        }

        if (value.length <= 3) {
            input.value = value;
        } else {
            input.value = value.replace(/(\d{3})(\d{0,3})/, '$1 $2');
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const otpInput = document.getElementById('otp');

        otpInput.addEventListener('input', function () {
            formatOtp(otpInput);
        });
    });
</script>

</body>
</html>

