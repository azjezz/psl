<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_ord;

/**
 * Get code point of character.
 *
 * Example:
 *
 *      Str\ord('H')
 *      => Int(72)
 *
 *      Str\ord('ل')
 *      => Int(1604)
 *
 * @pure
 *
 * @api
 */
function ord(string $character, Encoding $encoding = Encoding::Utf8): int
{
    return mb_ord($character, $encoding->value);
}
