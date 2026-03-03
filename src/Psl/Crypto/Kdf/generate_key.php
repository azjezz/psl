<?php

declare(strict_types=1);

namespace Psl\Crypto\Kdf;

use Psl\Crypto\Internal;

use function sodium_crypto_kdf_keygen;

/**
 * Generate a new random KDF master key.
 */
function generate_key(): Key
{
    return new Key(Internal\call_sodium(fn() => sodium_crypto_kdf_keygen()));
}
