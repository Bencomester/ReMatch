<?php
require_once '../includes/config.php';
require_once '../includes/signup_contr.inc.php';
require_once '../includes/security.php';
require_once "../lang/index.php";

// Check if the user is logged in by verifying session variables
if (isset($_SESSION['logged-in'])) {
    header("Location: ..");
    exit();
}
$err = $_SESSION["error_signup"] ?? [];
$data = $_SESSION["signup_post"] ?? [];
unset($_SESSION["error_signup"]);
unset($_SESSION["signup_post"]);

$texts = initLang();

if (isset($_GET["newemail"]) && !!filter_var($_GET["newemail"], FILTER_VALIDATE_EMAIL)) {
    $email = $_GET["newemail"];
    if (inVerification($email)) sendVerificationEmail($email);
    header("Location: index.php?signup=success");
}
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en';?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../styles/languagedropdown.css">
    <link rel="stylesheet" href="../styles/intl-tel-input.css">
    <link rel="stylesheet" href="../node_modules/intl-tel-input/build/css/intlTelInput.min.css">
    <script src="../node_modules/intl-tel-input/build/js/intlTelInputWithUtils.min.js"></script>
    <script src="../node_modules/intl-tel-input/build/js/utils.js" type="module"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <title><?= $texts["title-signup"] ?></title>
</head>

<body class="signup">
<?= languageDropdown() ?>
<div class="login-container">
    <h2><?= $texts["sign-up"] ?></h2>
    <?php
    if (($_GET["signup"] ?? "") == "success") {
        echo '<div class="login-success">' . $texts["signup-success"] . '</div>';
    }
    ?>
    <form id="signupForm" action="../includes/signup.inc.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <label for="lastname"><?= $texts["last-name"] ?></label>
            <input
                    type="text"
                    name="lastname"
                    id="lastname"
                    placeholder="<?= $texts["example-last-name"]?>"
                    value="<?= isset($data["lastname"]) ? htmlspecialchars($data["lastname"]) : '';?>">
            <label for="firstname"><?= $texts["first-name"]?>*</label>
            <input
                    type="text"
                    name="firstname"
                    id="firstname"
                    placeholder="<?= $texts["example-first-name"]?>"
                    value="<?= isset($data["firstname"]) ? htmlspecialchars($data["firstname"]) : '';?>" required>
            <label for="username" class="<?= isset($err["username"]) ? 'error-message' : '';?>"><?= isset($err["username"]) ? $texts[$err["username"]] : $texts["username"] . '*';?></label>
            <input
                type="text"
                name="username"
                id="username"
                placeholder="<?= $texts["example-username"]?>"
                class="<?= isset($err["username"]) ? 'error' : '';?>"
                value="<?= isset($data["username"]) ? htmlspecialchars($data["username"]) : '';?>" required>
            <label for="email" class="<?= isset($err["email"]) ? 'error-message' : '';?>"><?= isset($err["email"]) ? $texts[$err["email"]] : $texts["email"] . '*';?></label>
            <input
                type="email"
                name="email"
                id="email"
                placeholder="<?= $texts["example-email"]?>"
                class="<?= isset($err["email"]) ? 'error' : '';?>"
                value="<?= isset($data["email"]) ? htmlspecialchars($data["email"]) : '';?>" required>
            <label for="phone" class="<?= isset($err["phone"]) ? 'error-message' : '';?>"><?= isset($err["phone"]) ? $texts[$err["phone"]] : $texts["phone"];?></label>
            <div class="phone-input-container">
                <input type="hidden" name="telephone" id="telephone">
                <input
                    type="tel"
                    name="phone"
                    id="phone"
                    class="<?= isset($err["phone"]) ? 'error' : '';?>"
                    value="<?= isset($data["phone"]) ? htmlspecialchars($data["phone"]) : '';?>">
            </div>
            <label for="password" class="<?= isset($err["password"]) ? 'error-message' : '';?>"><?= isset($err["password"]) ? $texts[$err["password"]] : $texts["password"] . '*';?></label>
            <input
                type="password"
                name="password"
                id="password"
                placeholder="<?= $texts["enter-password"]?>"
                oninput="checkPasswordStrength()"
                class="<?= isset($err["password"]) ? 'error' : '';?>" required>
            <label for="password2" class="<?= isset($err["pwd"]) ? 'error-message' : '';?>"><?= isset($err["pwd"]) ? $texts[$err["pwd"]] : $texts["confirm-password"] . '*';?></label>
        <input
                type="password"
                name="password2"
                id="password2"
                placeholder="<?= $texts["confirm-password"] ?>"
                class="<?= isset($err["pwd"]) ? 'error' : ''; ?>" required>
        <div id="requirements">
            <p class="requirement" id="length-requirement"><span class="icon">✗</span> <?= $texts["pwd-8-chars"] ?></p>
            <p class="requirement" id="uppercase-requirement"><span class="icon">✗</span> <?= $texts["pwd-upper"] ?></p>
            <p class="requirement" id="lowercase-requirement"><span class="icon">✗</span> <?= $texts["pwd-lower"] ?></p>
            <p class="requirement" id="number-requirement"><span class="icon">✗</span> <?= $texts["pwd-number"] ?></p>
        </div>
        <div class="cf-turnstile" data-sitekey="0x4AAAAAAAzcQ_AbxIt5mw3W" data-theme="light"></div>
        <button type="submit" class="submit" id="submit" data-loading-text="Loading...">
            <span class="button-text"><?= $texts["sign-up"] ?></span>
            <span class="spinner"></span>
        </button>
    </form>
    <a href=".."><?= $texts["i-have-an-account"] ?></a>
</div>

<!-- Include JS -->
<script type="module">

    document.addEventListener("DOMContentLoaded", function () {
        const phoneInput = document.getElementById("phone");
        const telephoneInput = document.getElementById("telephone")
        // Initialize IntlTelInput
        const iti = intlTelInput(phoneInput, {
            initialCountry: "hu",
            preferredCountries: ["hu"],
            loadUtilsOnInit: true,
            formatOnDisplay: true,
            separateDialCode: true,
            autoPlaceholder: "polite",
            nationalMode: false,
            utilsScript: "../node_modules/intl-tel-input/build/js/utils.js",
        });

        // Prevent form submission if the number is invalid
        const form = document.getElementById("signupForm");
        form.addEventListener("submit", function (event) {
            if (!iti.isValidNumber() && phoneInput.value) {
                alert("Please enter a valid phone number.");
                event.preventDefault();
            } else {
                // Use the full international number for backend processing
                telephoneInput.value = iti.getNumber(); // E164 format for backend
                const submitButton = document.getElementById("submit");
                submitButton.disabled = true;
                submitButton.classList.add('loading');
            }
        });
    });
</script>

<script>
    function checkPasswordStrength() {
        const password = document.getElementById("password").value;

        // Define password strength criteria
            const lengthCheck = password.length >= 8;
            const upperCheck = /[A-Z]/.test(password);
            const lowerCheck = /[a-z]/.test(password);
            const numberCheck = /[0-9]/.test(password);

            // Update each requirement based on whether the criteria are met
            toggleRequirement("length-requirement", lengthCheck);
            toggleRequirement("uppercase-requirement", upperCheck);
            toggleRequirement("lowercase-requirement", lowerCheck);
            toggleRequirement("number-requirement", numberCheck);
        }

        function toggleRequirement(elementId, isMet) {
            const requirement = document.getElementById(elementId);
            const icon = requirement.querySelector(".icon");

            if (isMet) {
                requirement.classList.add("met");
                icon.textContent = "✓"; // Set tick icon
            } else {
                requirement.classList.remove("met");
                icon.textContent = "✗"; // Set cross icon
            }
        }
    </script>

</body>

</html>