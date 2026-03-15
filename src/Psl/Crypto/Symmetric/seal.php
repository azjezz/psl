<?php

declare(strict_types=1);

namespace Psl\Crypto\Symmetric;

use Psl\Crypto\Exception;
use Psl\SecureRandom;
use SensitiveParameter;

/**
 * Encrypt a plaintext message with a symmetric key.
 *
 * @throws Exception\RuntimeException If encryption fails.
 * @throws SecureRandom\Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy for nonce generation.
 */
function seal(
    #[SensitiveParameter]
    string $plaintext,
    #[SensitiveParameter]
    Key $key,
    string $additionalData = '',
): string {
    return new Encryptor($key)->seal($plaintext, $additionalData);
}
