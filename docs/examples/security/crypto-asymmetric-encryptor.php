<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Asymmetric;
use Psl\IO;

$keyPair = Asymmetric\generate_key_pair();

// The Encryptor class implements the EncryptorInterface for sealed-box encryption.
// Useful when you need an object to pass around or inject as a dependency.
$encryptor = new Asymmetric\Encryptor($keyPair->secretKey, $keyPair->publicKey);

$ciphertext = $encryptor->seal('Encrypt me');
$plaintext = $encryptor->open($ciphertext);

IO\write_line('Decrypted: %s', $plaintext);
