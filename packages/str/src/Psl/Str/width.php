<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_strwidth;

/**
 * Return the display width of a string as defined by {@see mb_strwidth()}.
 *
 * For codepoint counting, use {@see length()}.
 * For grapheme cluster counting, use {@see Grapheme\length()}.
 *
 * @pure
 */
function width(string $string, Encoding $encoding = Encoding::Utf8): int
{
    return mb_strwidth($string, $encoding->value);
}
