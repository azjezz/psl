<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Return a specific character.
 */
function chr(int $ascii): string
{
    return \chr($ascii);
}
