<?php

declare(strict_types=1);

namespace Psl\Crypto\Symmetric;

use Psl\Crypto;
use Psl\Crypto\Exception;
use Psl\SecureRandom;
use SensitiveParameter;

interface EncryptorInterface extends Crypto\EncryptorInterface
{
    /**
     * Encrypt a plaintext message with optional additional authenticated data.
     *
     * @throws Exception\RuntimeException If encryption fails.
     * @throws SecureRandom\Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy for nonce generation.
     */
    public function seal(#[SensitiveParameter] string $plaintext, string $additionalData = ''): string;

    /**
     * Decrypt a ciphertext message with optional additional authenticated data.
     *
     * @throws Exception\DecryptionException If decryption fails.
     */
    public function open(#[SensitiveParameter] string $ciphertext, string $additionalData = ''): string;
}
