<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Asymmetric;
use Psl\IO;

$keyPair = Asymmetric\generate_key_pair();

// Sealed boxes: encrypt with just a public key (anonymous sender).
// Only the holder of the secret key can decrypt.
$ciphertext = Asymmetric\seal('Anonymous tip: the cake is a lie.', $keyPair->publicKey);
$plaintext = Asymmetric\open($ciphertext, $keyPair->secretKey, $keyPair->publicKey);

IO\write_line('Decrypted: %s', $plaintext);
