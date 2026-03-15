<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Aead;
use Psl\IO;
use Psl\SecureRandom;

$algorithm = Aead\Algorithm::XChaCha20Poly1305;
$key = Aead\generate_key($algorithm);

// AEAD requires a unique nonce for each message.
// Never reuse a nonce with the same key.
$nonce = SecureRandom\bytes(24);

$ciphertext = Aead\encrypt(
    'Sensitive data',
    $key,
    nonce: $nonce,
    additionalData: 'context-info',
    algorithm: $algorithm,
);

$plaintext = Aead\decrypt($ciphertext, $key, $nonce, 'context-info', $algorithm);
IO\write_line('Decrypted: %s', $plaintext);
