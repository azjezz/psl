<?php

declare(strict_types=1);

namespace Psl\Str;

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
 */
function ord(string $char): int
{
    /** @var int $ascii */
    $ascii = \mb_ord($char, encoding($char));

    return $ascii;
}
