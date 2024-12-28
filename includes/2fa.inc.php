<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use OTPHP\TOTP;

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/dhb.inc.php";
require_once __DIR__ . "/login.inc.php";

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    $enabled = (is2faOnAndEnabled() ?? 0) == 1;
    error_log($enabled);
    $otp = str_replace(" ", "", $_POST["otp"]);
    $error = !run2tfa($otp, !$enabled);
    if ($error) {
        header("Location: ../2fa.php?enable=failure");
        exit();
    } else {
        if ($enabled) {
            loginSuccessful(useridQuery($_SESSION["userid"]));
            exit();
        }
        header("Location: ../2fa.php?enable=success");
    }
}

function is2faOnAndEnabled()
{
    $enabled = returnQuery("SELECT enabled FROM 2fa WHERE userid = :userid", ["userid" => $_SESSION["userid"]]);
    return empty($enabled) ? null : $enabled["enabled"];
}

function disable2fa(): void
{
    doQuery("UPDATE 2fa SET enabled = 0 WHERE :userid = userid;", ["userid" => $_SESSION["userid"]]);
    header("Location: myprofile");
}

function create2fa(): string
{
    // Generate a TOTP instance
    $totp = TOTP::create();

    // Get the generated secret key
    $secret = $totp->getSecret();

    // Set additional properties
    $totp->setLabel('ReMatch');
    $totp->setIssuer('rematch.com');

    // Generate a QR code URL for the user to scan
    $qrCodeUrl = $totp->getProvisioningUri();

    doQuery("INSERT INTO 2fa (userid, secret, enabled, timestamp) values (:userid, :secret, 0, NOW())", ["userid" => $_SESSION["userid"], "secret" => $secret]);

    // Display the QR code using a service like Google Chart API
    return base64_encode(createQR($qrCodeUrl)->getString());
}

function getSecretQR(): string
{
    $secret = returnQuery("SELECT secret FROM 2fa WHERE userid = :userid", ["userid" => $_SESSION["userid"]])["secret"];
    $totp = TOTP::create($secret);
    $totp->setLabel('ReMatch');
    $totp->setIssuer('rematch.com');
    return base64_encode(createQR($totp->getProvisioningUri())->getString());
}

function verify2fa($userOtp): bool
{
    $secret = returnQuery("SELECT secret FROM 2fa WHERE userid = :userid", ["userid" => $_SESSION["userid"]])["secret"];

    $totp = TOTP::create($secret);
    $totp->setLabel('ReMatch');
    $totp->setIssuer('rematch.com');

    // Verify the OTP
    if ($totp->verify($userOtp)) return true;
    return false;
}

function enable2fa($userOtp) : bool {
    if (verify2fa($userOtp)) {
        doQuery("UPDATE 2fa SET enabled = 1 WHERE userid = :userid ", ["userid" => $_SESSION["userid"]]);
        return true;
    }
    return false;
}

function run2tfa($userOtp, $isFirst) : bool {
    if ($isFirst) {
        return enable2fa($userOtp);
    } else {
        return verify2fa($userOtp);
    }
}

function createQR($qrCodeUrl) {
    $builder = new Builder(
        writer: new PngWriter(),
        writerOptions: [],
        validateResult: false,
        data: $qrCodeUrl,
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10,
        roundBlockSizeMode: RoundBlockSizeMode::Margin,
        labelText: 'Scan with Authenticator',
        labelFont: new OpenSans(20),
        labelAlignment: LabelAlignment::Center,
        logoPath: __DIR__.'/../image.png',
        logoResizeToWidth: 50,
        logoPunchoutBackground: true
    );

    try {
        return $builder->build();
    } catch (ValidationException $e) {
        die($e->getMessage());
    }
}