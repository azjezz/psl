<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_strimwidth;

/**
 * Returns a substring truncated to the given display width.
 *
 * @param int<0, max> $offset Codepoint offset to start from.
 * @param int<0, max> $width Maximum display width of the result.
 *
 * @pure
 */
function width_slice(string $string, int $offset, int $width, Encoding $encoding = Encoding::Utf8): string
{
    return mb_strimwidth($string, $offset, $width, '', $encoding->value);
}
