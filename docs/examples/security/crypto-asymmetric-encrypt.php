<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Asymmetric;
use Psl\IO;

// Both parties generate key pairs
$alice = Asymmetric\generate_key_pair();
$bob = Asymmetric\generate_key_pair();

// Alice sends an authenticated message to Bob
$ciphertext = Asymmetric\encrypt(
    'Hello Bob, this is Alice.',
    sender_secret_key: $alice->secretKey,
    recipient_public_key: $bob->publicKey,
);

// Bob decrypts and verifies it came from Alice
$plaintext = Asymmetric\decrypt(
    $ciphertext,
    recipient_secret_key: $bob->secretKey,
    sender_public_key: $alice->publicKey,
);

IO\write_line('From Alice: %s', $plaintext);
