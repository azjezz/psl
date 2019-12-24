<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Get truncated string with specified width.
 *
 * @param int         $offset      The start position offset. Number of
 *                                 characters from the beginning of string. (First character is 0)
 * @param int         $width       the width of the desired trim
 * @param string|null $trim_marker a string that is added to the end of string
 *                                 when string is truncated
 *
 * @return string The truncated string. If trim_marker is set,
 *                trim_marker is appended to the return value.
 */
function truncate(string $str, int $offset, int $width, ?string $trim_marker = null): string
{
    $offset = Psl\Internal\validate_offset($offset, length($str));

    return mb_strimwidth($str, $offset, $width, $trim_marker ?? '', encoding($str));
}
