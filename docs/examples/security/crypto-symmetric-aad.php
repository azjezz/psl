<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Exception;
use Psl\Crypto\Symmetric;
use Psl\IO;

$key = Symmetric\generate_key();

// Additional authenticated data (AAD) is authenticated but not encrypted.
// Use it for metadata that must be verified alongside the ciphertext.
$ciphertext = Symmetric\seal('secret payload', $key, additionalData: 'user-id:42');

// Decryption succeeds only if the same AAD is provided
$plaintext = Symmetric\open($ciphertext, $key, additionalData: 'user-id:42');
IO\write_line('Decrypted: %s', $plaintext);

// Wrong or missing AAD causes decryption to fail
try {
    Symmetric\open($ciphertext, $key, additionalData: 'user-id:99');
} catch (Exception\DecryptionException) {
    IO\write_line('Decryption failed: AAD mismatch.');
}
