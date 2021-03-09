<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function str_rot13;

/**
 * Perform the rot13 transform on a string.
 *
 * @pure
 */
function rot13(string $string): string
{
    return str_rot13($string);
}
