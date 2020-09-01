<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_ord;

/**
 * Return ASCII value of character.
 *
 * Example:
 *
 *      Str\ord('H')
 *      => Int(72)
 *
 *      Str\ord('ل')
 *      => Int(1604)
 *
 * @psalm-pure
 */
function ord(string $char): int
{
    return mb_ord($char, encoding($char));
}
