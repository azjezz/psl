<?php

declare(strict_types=1);

namespace Psl\Crypto\Aead;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_aead_aes256gcm_encrypt;
use function sodium_crypto_aead_aes256gcm_is_available;
use function sodium_crypto_aead_chacha20poly1305_ietf_encrypt;
use function sodium_crypto_aead_xchacha20poly1305_ietf_encrypt;

/**
 * Encrypt a plaintext message using AEAD with an explicit nonce.
 *
 * @throws Exception\RuntimeException If encryption fails or AES-256-GCM is not available.
 */
function encrypt(
    #[SensitiveParameter]
    string $plaintext,
    #[SensitiveParameter]
    Key $key,
    #[SensitiveParameter]
    string $nonce,
    string $additionalData,
    Algorithm $algorithm,
): string {
    return match ($algorithm) {
        Algorithm::Aes256Gcm => (static function () use ($plaintext, $key, $nonce, $additionalData): string {
            // @codeCoverageIgnoreStart
            if (!sodium_crypto_aead_aes256gcm_is_available()) {
                throw new Exception\RuntimeException('AES-256-GCM is not available on this platform.');
            }

            // @codeCoverageIgnoreEnd

            return Internal\call_sodium(fn() => sodium_crypto_aead_aes256gcm_encrypt(
                $plaintext,
                $additionalData,
                $nonce,
                $key->bytes,
            ));
        })(),
        Algorithm::XChaCha20Poly1305 => Internal\call_sodium(fn() => sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $additionalData,
            $nonce,
            $key->bytes,
        )),
        Algorithm::ChaCha20Poly1305 => Internal\call_sodium(fn() => sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
            $plaintext,
            $additionalData,
            $nonce,
            $key->bytes,
        )),
    };
}
