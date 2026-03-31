<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function ord as php_ord;

/**
 * Return ASCII value of character.
 *
 * @return int<0, 255>
 *
 * @pure
 *
 * @api
 */
function ord(string $character): int
{
    return $character !== '' ? php_ord($character[0]) : 0;
}
