<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\MIME\DKIM;

/** @var string $rsaPrivateKeyPem */

$config = new DKIM\SigningConfiguration(
    domain: 'example.com',
    selector: 'default',
    algorithm: DKIM\Algorithm::RsaSha256,
    headerCanonicalization: DKIM\Canonicalization::Relaxed,
    bodyCanonicalization: DKIM\Canonicalization::Relaxed,
);

$signer = new DKIM\Signer($rsaPrivateKeyPem, $config);

$rawMessage = "From: sender@example.com\r\nTo: rcpt@example.com\r\nSubject: Test\r\n\r\nHello!";
$signedMessage = $signer->sign($rawMessage);

// $signedMessage now has a DKIM-Signature header prepended
