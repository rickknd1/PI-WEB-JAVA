<?php

namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use OTPHP\TOTP;

class GoogleAuthenticatorService
{
    public function generateSecret(): string
    {
        return TOTP::generate()->getSecret();
    }

    public function generateQrCode(string $email, string $secret): string
    {
        $totp = TOTP::create($secret);
        $totp->setLabel($email);
        $totp->setIssuer('SyncYLinkY');

        $qrCode = QrCode::create($totp->getProvisioningUri());
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return base64_encode($result->getString());
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $totp = TOTP::create($secret);
        return $totp->verify($code);
    }
}