<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use Psl\Crypto\Internal;

use function sodium_crypto_sign_keypair;
use function sodium_crypto_sign_publickey;
use function sodium_crypto_sign_secretkey;
use function sodium_memzero;

/**
 * Generate a new Ed25519 signing key pair.
 */
function generate_key_pair(): KeyPair
{
    $keypair = Internal\call_sodium(fn() => sodium_crypto_sign_keypair());
    $secretKey = new SecretKey(Internal\call_sodium(fn() => sodium_crypto_sign_secretkey($keypair)));
    $publicKey = new PublicKey(Internal\call_sodium(fn() => sodium_crypto_sign_publickey($keypair)));
    sodium_memzero($keypair);

    return new KeyPair($publicKey, $secretKey);
}
