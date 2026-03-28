<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strtoupper;

/**
 * Returns the string with all alphabetic characters converted to uppercase.
 *
 * @pure
 *
 * @api
 */
function uppercase(string $string): string
{
    return strtoupper($string);
}
