<?php

declare(strict_types=1);

namespace Psl\Crypto\Hkdf;

use Psl\Crypto\Exception;
use Psl\Hash\Hmac;
use SensitiveParameter;

use function sodium_memzero;

/**
 * HKDF: Derive a key using extract-then-expand.
 *
 * @param positive-int $length Desired output length in bytes.
 *
 * @throws Exception\RuntimeException If the requested length exceeds the maximum.
 *
 * @see https://tools.ietf.org/html/rfc5869
 *
 * @return non-empty-string
 */
function derive(
    #[SensitiveParameter]
    string $inputKeyingMaterial,
    string $salt = '',
    string $info = '',
    int $length = 32,
    Hmac\Algorithm $algorithm = Hmac\Algorithm::Sha256,
): string {
    $pseudoRandomKey = namespace\extract($inputKeyingMaterial, $salt, $algorithm);

    try {
        return namespace\expand($pseudoRandomKey, $info, $length, $algorithm);
    } finally {
        sodium_memzero($pseudoRandomKey);
    }
}
