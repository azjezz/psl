<?php

declare(strict_types=1);

namespace Psl\Crypto\Hkdf;

use Psl\Encoding\Hex;
use Psl\Hash\Hmac;
use SensitiveParameter;

use function str_repeat;

/**
 * HKDF-Extract: Extract a pseudorandom key from input keying material.
 *
 * @see https://tools.ietf.org/html/rfc5869#section-2.2
 *
 * @return non-empty-string
 */
function extract(
    #[SensitiveParameter]
    string $inputKeyingMaterial,
    string $salt = '',
    Hmac\Algorithm $algorithm = Hmac\Algorithm::Sha256,
): string {
    if ($salt === '') {
        /** @var non-negative-int $hashLength */
        $hashLength = match ($algorithm) {
            Hmac\Algorithm::Sha384 => 48,
            Hmac\Algorithm::Sha512 => 64,
            default => 32,
        };

        /** @var non-empty-string $salt */
        $salt = str_repeat("\x00", $hashLength);
    }

    /** @var non-empty-string */
    return Hex\decode(Hmac\hash($inputKeyingMaterial, $algorithm, $salt));
}
