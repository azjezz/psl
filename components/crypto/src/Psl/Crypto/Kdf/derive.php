<?php

declare(strict_types=1);

namespace Psl\Crypto\Kdf;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_kdf_derive_from_key;
use function strlen;

/**
 * Derive a sub-key from a master key using sodium KDF.
 *
 * @param positive-int     $subKeyId The sub-key identifier (0-based).
 * @param non-empty-string $context    An 8-byte context string.
 * @param int<16, 64>      $length     The desired sub-key length in bytes (16-64).
 *
 * @throws Exception\RuntimeException If the context is not exactly {@see CONTEXT_BYTES} bytes.
 *
 * @return non-empty-string
 */
function derive(#[SensitiveParameter] Key $key, int $subKeyId, string $context, int $length = 32): string
{
    if (strlen($context) !== namespace\CONTEXT_BYTES) {
        throw new Exception\RuntimeException('KDF context must be exactly ' . namespace\CONTEXT_BYTES . ' bytes.');
    }

    /** @var non-empty-string */
    return Internal\call_sodium(fn() => sodium_crypto_kdf_derive_from_key($length, $subKeyId, $context, $key->bytes));
}
