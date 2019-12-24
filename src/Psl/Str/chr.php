<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Return a specific character.
 *
 * Example:
 *
 *      Str\chr(72)
 *      => Str('H')
 *
 *      Str\chr(1604)
 *      => Str('ل')
 */
function chr(int $ascii): string
{
    /** @var string $char */
    $char = \mb_chr($ascii, 'UTF-8');

    Psl\invariant(is_string($char), 'Unexpected Error.');

    return $char;
}
