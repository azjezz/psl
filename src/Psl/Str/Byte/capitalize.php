<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function ucfirst;

/**
 * Returns the string with the first character capitalized.
 *
 * If the first character is already capitalized or isn't alphabetic, the string
 * will be unchanged.
 *
 * @pure
 */
function capitalize(string $string): string
{
    return ucfirst($string);
}
