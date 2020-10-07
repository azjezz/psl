<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Return ASCII value of character.
 *
 * @psalm-pure
 */
function ord(string $character): int
{
    return \ord($character);
}
