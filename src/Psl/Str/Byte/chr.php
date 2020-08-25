<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Return a specific character.
 *
 * @psalm-pure
 */
function chr(int $ascii): string
{
    return \chr($ascii);
}
