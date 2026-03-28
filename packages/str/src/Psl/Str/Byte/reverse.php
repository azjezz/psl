<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strrev;

/**
 * Reverses the string.
 *
 * @pure
 *
 * @api
 */
function reverse(string $string): string
{
    return strrev($string);
}
