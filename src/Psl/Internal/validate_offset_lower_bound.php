<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl;

/**
 * Verifies that the `$offset` is not less than minus `$length`. Returns the
 * offset as a positive integer.
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $offset is out-of-bounds.
 *
 * @internal
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
