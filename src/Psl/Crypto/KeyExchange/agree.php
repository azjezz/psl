<?php

declare(strict_types=1);

namespace Psl\Crypto\KeyExchange;

use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_scalarmult;
use function sodium_memzero;

/**
 * Perform X25519 Diffie-Hellman key agreement.
 *
 * Returns the shared secret computed from a local secret key and a remote public key.
 */
function agree(#[SensitiveParameter] SecretKey $secret_key, #[SensitiveParameter] PublicKey $public_key): SharedSecret
{
    $raw = Internal\call_sodium(fn() => sodium_crypto_scalarmult($secret_key->bytes, $public_key->bytes));
    $result = new SharedSecret($raw);
    sodium_memzero($raw);

    return $result;
}
