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
 */
function derive(
    #[SensitiveParameter] string $input_keying_material,
    string $salt = '',
    string $info = '',
    int $length = 32,
    Hmac\Algorithm $algorithm = Hmac\Algorithm::Sha256,
): string {
    $pseudo_random_key = namespace\extract($input_keying_material, $salt, $algorithm);

    try {
        return namespace\expand($pseudo_random_key, $info, $length, $algorithm);
    } finally {
        sodium_memzero($pseudo_random_key);
    }
}
