<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_chr;

/**
 * Return a specific character.
 *
 * Example:
 *
 *      Str\chr(72)
 *      => Str('H')
 *
 *      Str\chr(1604)
 *      => Str('ل')
 *
 * @pure
 */
function chr(int $codepoint, Encoding $encoding = Encoding::UTF_8): string
{
    return (string) mb_chr($codepoint, $encoding->value);
}
