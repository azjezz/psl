<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use Psl\Crypto;
use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_box_seal;
use function sodium_crypto_box_seal_open;
use function sodium_memzero;

final readonly class Encryptor implements Crypto\EncryptorInterface
{
    public function __construct(
        #[SensitiveParameter]
        private SecretKey $secretKey,
        #[SensitiveParameter]
        private PublicKey $publicKey,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function seal(#[SensitiveParameter] string $plaintext): string
    {
        return Internal\call_sodium(fn() => sodium_crypto_box_seal($plaintext, $this->publicKey->bytes));
    }

    /**
     * {@inheritDoc}
     */
    public function open(#[SensitiveParameter] string $ciphertext): string
    {
        $keypair = $this->secretKey->bytes . $this->publicKey->bytes;

        try {
            $plaintext = Internal\call_sodium(static fn() => sodium_crypto_box_seal_open($ciphertext, $keypair));

            if ($plaintext === false) {
                throw new Exception\DecryptionException('Asymmetric decryption failed.');
            }

            return $plaintext;
        } finally {
            sodium_memzero($keypair);
        }
    }
}
