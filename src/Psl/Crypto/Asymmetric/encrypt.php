<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use Psl\SecureRandom;
use SensitiveParameter;

use function sodium_crypto_box;
use function sodium_memzero;

/**
 * Encrypt a message for a recipient using authenticated public-key encryption.
 *
 * A random nonce is generated and prepended to the ciphertext.
 *
 * @throws Exception\RuntimeException If encryption fails.
 * @throws SecureRandom\Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy for nonce generation.
 */
function encrypt(
    #[SensitiveParameter]
    string $plaintext,
    #[SensitiveParameter]
    SecretKey $sender_secret_key,
    #[SensitiveParameter]
    PublicKey $recipient_public_key,
): string {
    $nonce = SecureRandom\bytes(namespace\NONCE_BYTES);
    $key_pair = $sender_secret_key->bytes . $recipient_public_key->bytes;

    try {
        $ciphertext = Internal\call_sodium(fn() => sodium_crypto_box($plaintext, $nonce, $key_pair));

        return $nonce . $ciphertext;
    } finally {
        sodium_memzero($key_pair);
    }
}
