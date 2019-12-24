<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Psl\Internal;

use Psl;

const ALPHABET_BASE64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
const ALPHABET_BASE64_URL = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

/**
 * Verifies that the `$offset` is within plus/minus `$length`. Returns the
 * offset as a positive integer.
 */
function validate_offset(int $offset, int $length): int
{
    $original_offset = $offset;

    if ($offset < 0) {
        $offset += $length;
    }

    Psl\invariant($offset >= 0 && $offset <= $length, 'Offset (%d) was out-of-bounds.', $original_offset);

    return $offset;
}

/**
 * Verifies that the `$offset` is not less than minus `$length`. Returns the
 * offset as a positive integer.
 */
function validate_offset_lower_bound(int $offset, int $length): int
{
    $original_offset = $offset;

    if ($offset < 0) {
        $offset += $length;
    }

    Psl\invariant($offset >= 0, 'Offset (%d) was out-of-bounds.', $original_offset);

    return $offset;
}

/**
 * @param mixed $val
 */
function boolean($val): bool
{
    return (bool) $val;
}
