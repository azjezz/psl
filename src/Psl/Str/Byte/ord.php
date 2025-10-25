<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Return ASCII value of character.
 *
 * @return int<0, 255>
 *
 * @pure
 */
function ord(string $character): int
{
    return $character !== '' ? \ord($character[0]) : 0;
}
