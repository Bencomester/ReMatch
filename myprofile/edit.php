<?php
require_once '../includes/config.php';
require_once '../includes/security.php';
// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['logged-in'])) {
    header("Location: ..");
    exit();
}

require('../includes/dhb.inc.php');
include("../lang/index.php");
if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'en';
$texts = include "../lang/$lang.php";

$query = "SELECT * FROM users WHERE id = :userid";
$data = returnQuery($query, ["userid" => $_SESSION['userid']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="editStyle.css">
    <link rel="stylesheet" href="../node_modules/intl-tel-input/build/css/intlTelInput.min.css">
    <script src="../node_modules/intl-tel-input/build/js/intlTelInputWithUtils.min.js"></script>
    <style>
        .iti--allow-dropdown {
            width: 100%;
            /* Make the input field full width */
        }
    </style>
</head>
<body>
<?= languageDropdown($texts["choose-language"], $_SESSION['lang'] ?? "en") ?>
<!-- Edit Profile Container -->
<div class="edit-profile-container">
    <h2>Edit Profile</h2>
    <form id="updateForm" action="update.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <!-- Profile Picture Upload -->
        <div class="profile-picture-section">
            <label for="profile-picture">Profile Picture</label>
            <div class="profile-picture-preview">
                <img id="profilePreview" src="<?= file_exists("../pfp/" . $_SESSION["userid"] . ".png") ? "../pfp/" . $_SESSION["userid"] . ".png" : (file_exists("../pfp/" . $_SESSION["userid"] . ".jpg") ? "../pfp/" . $_SESSION["userid"] . ".jpg" : "150.png") ?>" alt="Profile Picture">
            </div>
            <input type="file" id="profile-picture" name="profile-picture" accept="image/*" onchange="previewProfilePicture(event)">
        </div>

        <!-- Name Field -->
        <label for="lastname">Last Name</label>
        <input type="text" id="lastname" name="lastname" value="<?= $data["lastname"]?>">

        <label for="firstname">First Name*</label>
        <input type="text" id="firstname" name="firstname" value="<?= $data["firstname"]?>" required>

        <label for="username">Username*</label>
        <input type="text" id="username" name="username" value="<?= $data["username"]?>" required>

        <!-- Email Field -->
        <label for="email">Email*</label>
        <input type="email" id="email" name="email" value="<?= $data["email"]?>" required>

        <label for="phone">Telephone</label>
        <div class="phone-input-container">
            <input type="hidden" name="telephone" id="telephone">
            <input
                    type="tel"
                    name="phone"
                    id="phone"
                    value="<?= $data["phone"]?>">
        </div>
        <?php
        if (!empty($_SESSION["edit-error"])) {
            echo '<div class="edit-error">' . array_values($_SESSION["edit-error"])[0] . '</div>';
            unset($_SESSION["edit-error"]);
        }
        if ($_GET["success"] ?? false) {
            echo '<div class="edit-success">' . $texts["edit-success"] . '</div>';
        }
        ?>
        <!-- Save Changes Button -->
        <button type="submit" class="btn save-btn">Save Changes</button>
        <a href="." class="btn cancel-btn">Cancel</a>
    </form>
</div>

<!-- JavaScript for Image Preview -->
<script type="module">
    import intlTelInputUtils from "../node_modules/intl-tel-input/build/js/utils.js";

    document.addEventListener("DOMContentLoaded", function() {
        const phoneInput = document.getElementById("phone");
        const telephoneInput =  document.getElementById("telephone")
        // Initialize IntlTelInput
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "hu",
            loadUtilsOnInit: true,
            formatOnDisplay: true,
            separateDialCode: true,
            autoPlaceholder: "polite",
            preferredCountries: ['hu'],
            nationalMode: false,
        });

        // Prevent form submission if the number is invalid
        const form = document.getElementById("updateForm");
        form.addEventListener("submit", function(event) {
            if (!iti.isValidNumber()  && phoneInput.value) {
                alert("Please enter a valid phone number.");
                event.preventDefault();
            } else {
                // Use the full international number for backend processing
                telephoneInput.value = iti.getNumber(intlTelInputUtils.numberFormat.E164); // E164 format for backend
            }
        });
    });
</script>
<script>
    function previewProfilePicture(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('profilePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

</script>
</body>
</html>
