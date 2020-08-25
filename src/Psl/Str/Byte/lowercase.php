<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns the string with all alphabetic characters converted to lowercase.
 *
 * @psalm-pure
 */
function lowercase(string $lowercase): string
{
    return \strtolower($lowercase);
}
