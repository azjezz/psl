<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use Psl\Crypto\Internal;

use function sodium_crypto_box_keypair;
use function sodium_crypto_box_publickey;
use function sodium_crypto_box_secretkey;
use function sodium_memzero;

/**
 * Generate a new X25519 key pair for asymmetric encryption.
 */
function generate_key_pair(): KeyPair
{
    $keypair = Internal\call_sodium(fn() => sodium_crypto_box_keypair());
    /** @var non-empty-string $secretKeyBytes */
    $secretKeyBytes = Internal\call_sodium(fn() => sodium_crypto_box_secretkey($keypair));
    /** @var non-empty-string $publicKeyBytes */
    $publicKeyBytes = Internal\call_sodium(fn() => sodium_crypto_box_publickey($keypair));
    $secretKey = new SecretKey($secretKeyBytes);
    $publicKey = new PublicKey($publicKeyBytes);
    sodium_memzero($keypair);

    return new KeyPair($publicKey, $secretKey);
}
