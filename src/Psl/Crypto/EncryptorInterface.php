<?php

declare(strict_types=1);

namespace Psl\Crypto;

use SensitiveParameter;

interface EncryptorInterface
{
    /**
     * Encrypt a plaintext message.
     *
     * @throws Exception\RuntimeException If encryption fails.
     */
    public function seal(#[SensitiveParameter] string $plaintext): string;

    /**
     * Decrypt a ciphertext message.
     *
     * @throws Exception\DecryptionException If decryption fails.
     */
    public function open(#[SensitiveParameter] string $ciphertext): string;
}
