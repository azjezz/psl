<?php

declare(strict_types=1);

namespace Psl\Crypto\Symmetric;

use Psl\Crypto\Internal;

use function sodium_crypto_aead_xchacha20poly1305_ietf_keygen;

/**
 * Generate a new random symmetric encryption key.
 */
function generate_key(): Key
{
    return new Key(Internal\call_sodium(fn() => sodium_crypto_aead_xchacha20poly1305_ietf_keygen()));
}
