<?php

declare(strict_types=1);

namespace Psl\Crypto\Kdf;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use Psl\Str\Byte;
use SensitiveParameter;

use function sodium_crypto_kdf_derive_from_key;

/**
 * Derive a sub-key from a master key using sodium KDF.
 *
 * @param positive-int     $sub_key_id The sub-key identifier (0-based).
 * @param non-empty-string $context    An 8-byte context string.
 * @param int<16, 64>      $length     The desired sub-key length in bytes (16-64).
 *
 * @throws Exception\RuntimeException If the context is not exactly {@see CONTEXT_BYTES} bytes.
 *
 * @return non-empty-string
 */
function derive(#[SensitiveParameter] Key $key, int $sub_key_id, string $context, int $length = 32): string
{
    if (Byte\length($context) !== namespace\CONTEXT_BYTES) {
        throw new Exception\RuntimeException('KDF context must be exactly ' . namespace\CONTEXT_BYTES . ' bytes.');
    }

    /** @var non-empty-string */
    return Internal\call_sodium(fn() => sodium_crypto_kdf_derive_from_key($length, $sub_key_id, $context, $key->bytes));
}
