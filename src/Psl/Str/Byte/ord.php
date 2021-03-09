<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Return ASCII value of character.
 *
 * @pure
 */
function ord(string $character): int
{
    return \ord($character);
}
