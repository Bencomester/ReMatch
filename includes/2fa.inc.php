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

require_once __DIR__ . "/dhb.inc.php";
require_once __DIR__ . "/login.inc.php";

function is2faOnOrEnabled($userid): int
{
    $enabled = returnQuery("SELECT enabled FROM 2fa WHERE userid = :userid", ["userid" => $userid]);
    return empty($enabled) ? -1 : $enabled["enabled"];
}

function disable2fa($userid): void
{
    doQuery("UPDATE 2fa SET enabled = 0 WHERE :userid = userid;", ["userid" => $userid]);
}

function create2fa($userid): string
{
    $totp = TOTP::create();

    $secret = $totp->getSecret();

    $totp->setLabel('ReMatch');
    $totp->setIssuer('rematch.com');

    $qrCodeUrl = $totp->getProvisioningUri();

    doQuery("INSERT INTO 2fa (userid, secret, enabled, timestamp) values (:userid, :secret, 0, unix_timestamp())", ["userid" => $userid, "secret" => $secret]);

    return base64_encode(createQR($qrCodeUrl)->getString());
}

function getSecretQR($userid): string
{
    $secret = returnQuery("SELECT secret FROM 2fa WHERE userid = :userid", ["userid" => $userid])["secret"];
    $totp = TOTP::create($secret);
    $totp->setLabel('ReMatch');
    $totp->setIssuer('rematch.com');
    return base64_encode(createQR($totp->getProvisioningUri())->getString());
}

function verify2fa($userOtp, $userid): bool
{
    $secret = returnQuery("SELECT secret FROM 2fa WHERE userid = :userid", ["userid" => $userid])["secret"];

    $totp = TOTP::create($secret);
    $totp->setLabel('ReMatch');
    $totp->setIssuer('rematch.com');

    return $totp->verify($userOtp);
}

function enable2fa($userid) : void {
        doQuery("UPDATE 2fa SET enabled = 1 WHERE userid = :userid ", ["userid" => $userid]);  //TODO: update password time, for resetting tokens
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