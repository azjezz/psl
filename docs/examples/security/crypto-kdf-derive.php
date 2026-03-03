<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Kdf;
use Psl\IO;
use Psl\Str\Byte;

$masterKey = Kdf\generate_key();

// Derive subkeys for different purposes using a context string (exactly 8 bytes)
// and a numeric subkey ID.
$encryptionKey = Kdf\derive($masterKey, sub_key_id: 1, context: 'encryptn', length: 32);
$signingKey = Kdf\derive($masterKey, sub_key_id: 2, context: 'encryptn', length: 32);

IO\write_line('Encryption key length: %d bytes', Byte\length($encryptionKey));
IO\write_line('Signing key length: %d bytes', Byte\length($signingKey));
IO\write_line('Keys are different: %s', $encryptionKey !== $signingKey ? 'yes' : 'no');
