<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl;

/**
 * Verifies that the `$offset` is within plus/minus `$length`. Returns the
 * offset as a positive integer.
 *
 * @pure
 *
 * @codeCoverageIgnore
 *
 * @throws Psl\Exception\InvariantViolationException If the offset is out-of-bounds.
 *
 * @internal
 *
 * @return ($assert is true ? bool : int)
 */
function validate_offset(int $offset, int $length, bool $assert = false)
{
    $original_offset = $offset;

    if ($offset < 0) {
        $offset += $length;
    }

    Psl\invariant($offset >= 0 && $offset <= $length, 'Offset (%d) was out-of-bounds.', $original_offset);

    if (!$assert) {
        return $offset;
    }

    return true;
}
