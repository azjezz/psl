<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Hkdf;
use Psl\Hash\Hmac;
use Psl\IO;
use Psl\Str\Byte;

// HKDF (RFC 5869) derives keys from input keying material.
// Useful when you have a shared secret from key exchange and need session keys.
$inputKeyingMaterial = 'raw-shared-secret-from-key-exchange';

// One-step derivation (extract + expand combined)
$derivedKey = Hkdf\derive(
    input_keying_material: $inputKeyingMaterial,
    salt: 'optional-salt',
    info: 'session-encryption-key',
    length: 32,
    algorithm: Hmac\Algorithm::Sha256,
);

IO\write_line('Derived key length: %d bytes', Byte\length($derivedKey));

// Two-step derivation for deriving multiple keys from the same PRK
$prk = Hkdf\extract($inputKeyingMaterial, salt: 'my-salt');
$key1 = Hkdf\expand($prk, info: 'encryption', length: 32);
$key2 = Hkdf\expand($prk, info: 'authentication', length: 32);

IO\write_line('Keys are different: %s', $key1 !== $key2 ? 'yes' : 'no');
