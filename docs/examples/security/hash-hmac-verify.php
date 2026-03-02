<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Hash;
use Psl\Hash\Hmac;
use Psl\IO;
use Psl\SecureRandom;

/**
 * Simulate an API secret key (in practice, this should be stored securely and not hardcoded)
 * For demonstration purposes, we generate a random 32-byte string to use as the API secret key.
 */
$apiSecret = SecureRandom\string(32);

// Sender: sign the payload
$payload = '{"action":"transfer","amount":100}';
$signature = Hmac\hash($payload, Hmac\Algorithm::Sha256, $apiSecret);
IO\write_line('Signature: %s', $signature);

// Receiver: verify the signature
$receivedSignature = $signature; // simulate receiving the same signature
$expected = Hmac\hash($payload, Hmac\Algorithm::Sha256, $apiSecret);
if (!Hash\equals($expected, $receivedSignature)) {
    throw new RuntimeException('Invalid signature -- request may have been tampered with.');
}

IO\write_line('Signature verified successfully.');
