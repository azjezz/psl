<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function chr as php_chr;

/**
 * Return a specific character.
 *
 * @pure
 */
function chr(int $ascii): string
{
    return php_chr($ascii);
}
