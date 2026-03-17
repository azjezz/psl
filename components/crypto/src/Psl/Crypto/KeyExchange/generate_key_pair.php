<?php

declare(strict_types=1);

namespace Psl\Crypto\KeyExchange;

use Psl\Crypto\Internal;
use Psl\SecureRandom;

use function sodium_crypto_box_publickey_from_secretkey;

/**
 * Generate a new X25519 key pair for key exchange.
 *
 * @throws SecureRandom\Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy for key generation.
 */
function generate_key_pair(): KeyPair
{
    /** @var non-empty-string $secretKeyBytes */
    $secretKeyBytes = SecureRandom\bytes(namespace\SECRET_KEY_BYTES);
    /** @var non-empty-string $publicKeyBytes */
    $publicKeyBytes = Internal\call_sodium(fn() => sodium_crypto_box_publickey_from_secretkey($secretKeyBytes));

    return new KeyPair(new PublicKey($publicKeyBytes), new SecretKey($secretKeyBytes));
}
