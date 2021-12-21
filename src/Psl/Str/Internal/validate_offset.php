<?php

declare(strict_types=1);

namespace Psl\Str\Internal;

use Psl\Str\Exception;

/**
 * Verifies that the `$offset` is within plus/minus `$length`. Returns the
 * offset as a positive integer.
 *
 * @pure
 *
 * @codeCoverageIgnore
 *
 * @throws Exception\OutOfBoundsException If the offset is out-of-bounds.
 *
 * @internal
 *
 * @return ($assert is true ? true : int<0, max>)
 */
function validate_offset(int $offset, int $length, bool $assert = false): int|bool
{
    if (0 === $offset) {
        return $assert ? true : $offset;
    }

    $original_offset = $offset;

    if ($offset < 0) {
        $offset += $length;
    }

    if ($offset < 0 || $offset > $length) {
        throw Exception\OutOfBoundsException::for($original_offset);
    }

    if (!$assert) {
        /** @var int<0, max> */
        return $offset;
    }

    return true;
}
