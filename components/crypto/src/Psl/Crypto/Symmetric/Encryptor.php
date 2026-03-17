<?php

declare(strict_types=1);

namespace Psl\Crypto\Symmetric;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use Psl\SecureRandom;
use SensitiveParameter;

use function sodium_crypto_aead_xchacha20poly1305_ietf_decrypt;
use function sodium_crypto_aead_xchacha20poly1305_ietf_encrypt;
use function strlen;
use function substr;

final readonly class Encryptor implements EncryptorInterface
{
    public function __construct(
        #[SensitiveParameter]
        private Key $key,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function seal(#[SensitiveParameter] string $plaintext, string $additionalData = ''): string
    {
        $nonce = SecureRandom\bytes(namespace\NONCE_BYTES);
        $ciphertext = Internal\call_sodium(fn() => sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $additionalData,
            $nonce,
            $this->key->bytes,
        ));

        return $nonce . $ciphertext;
    }

    /**
     * {@inheritDoc}
     */
    public function open(#[SensitiveParameter] string $ciphertext, string $additionalData = ''): string
    {
        $minLength = namespace\NONCE_BYTES + namespace\TAG_BYTES;
        if (strlen($ciphertext) < $minLength) {
            throw new Exception\DecryptionException('Ciphertext is too short.');
        }

        $nonce = substr($ciphertext, 0, namespace\NONCE_BYTES);
        $encrypted = substr($ciphertext, namespace\NONCE_BYTES);

        $plaintext = Internal\call_sodium(fn() => sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $encrypted,
            $additionalData,
            $nonce,
            $this->key->bytes,
        ));

        if ($plaintext === false) {
            throw new Exception\DecryptionException('Decryption failed.');
        }

        return $plaintext;
    }
}
