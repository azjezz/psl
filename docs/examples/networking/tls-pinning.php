<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TLS;

// Note: Replace the fingerprints below with actual SHA-256 certificate fingerprints.
$config = TLS\ClientConfig::default()->withPeerFingerprints([
    'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2',
    'e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6',
]);

$tls = TLS\connect('api.example.com', 443, $config);
