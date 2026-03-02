<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TLS;

$tls = TLS\connect('example.com', 443);

$state = $tls->getState();
echo "TLS {$state->version->name}\n"; // "TLS Tls13"
echo "Cipher: {$state->cipherName}\n"; // "TLS_AES_256_GCM_SHA384"
echo "Bits: {$state->cipherBits}\n"; // 256
echo "ALPN: {$state->alpnProtocol}\n"; // "h2" or null

if ($state->peerCertificate !== null) {
    echo "Subject: {$state->peerCertificate->subject}\n";
    echo "Issuer: {$state->peerCertificate->issuer}\n";
    echo "Valid until: {$state->peerCertificate->validTo->toRfc3339()}\n";
}

$tls->close();
