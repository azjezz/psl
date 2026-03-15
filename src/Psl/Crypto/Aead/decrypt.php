<?php

declare(strict_types=1);

namespace Psl\Crypto\Aead;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_aead_aes256gcm_decrypt;
use function sodium_crypto_aead_aes256gcm_is_available;
use function sodium_crypto_aead_chacha20poly1305_ietf_decrypt;
use function sodium_crypto_aead_xchacha20poly1305_ietf_decrypt;

/**
 * Decrypt a ciphertext message using AEAD with an explicit nonce.
 *
 * @throws Exception\DecryptionException If decryption fails.
 * @throws Exception\RuntimeException If AES-256-GCM is not available.
 */
function decrypt(
    #[SensitiveParameter]
    string $ciphertext,
    #[SensitiveParameter]
    Key $key,
    #[SensitiveParameter]
    string $nonce,
    string $additionalData,
    Algorithm $algorithm,
): string {
    $result = match ($algorithm) {
        Algorithm::Aes256Gcm => (static function () use ($ciphertext, $key, $nonce, $additionalData): string|false {
            // @codeCoverageIgnoreStart
            if (!sodium_crypto_aead_aes256gcm_is_available()) {
                throw new Exception\RuntimeException('AES-256-GCM is not available on this platform.');
            }

            // @codeCoverageIgnoreEnd

            return Internal\call_sodium(fn() => sodium_crypto_aead_aes256gcm_decrypt(
                $ciphertext,
                $additionalData,
                $nonce,
                $key->bytes,
            ));
        })(),
        Algorithm::XChaCha20Poly1305 => Internal\call_sodium(fn() => sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            $additionalData,
            $nonce,
            $key->bytes,
        )),
        Algorithm::ChaCha20Poly1305 => Internal\call_sodium(fn() => sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
            $ciphertext,
            $additionalData,
            $nonce,
            $key->bytes,
        )),
    };

    if ($result === false) {
        throw new Exception\DecryptionException('AEAD decryption failed.');
    }

    return $result;
}
