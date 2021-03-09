<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_strimwidth;

/**
 * Get truncated string with specified width.
 *
 * @param int $offset The start position offset. Number of
 *                    characters from the beginning of string. (First character is 0)
 * @param int $width the width of the desired trim
 * @param string|null $trim_marker a string that is added to the end of string
 *                                 when string is truncated
 *
 * @throws Psl\Exception\InvariantViolationException If the offset is out-of-bounds.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 *
 * @return string The truncated string. If trim_marker is set,
 *                trim_marker is appended to the return value.
 *
 * @pure
 */
function truncate(
    string $string,
    int $offset,
    int $width,
    ?string $trim_marker = null,
    ?string $encoding = null
): string {
    $offset = Internal\validate_offset($offset, length($string, $encoding));

    return mb_strimwidth($string, $offset, $width, $trim_marker ?? '', Internal\internal_encoding($encoding));
}
