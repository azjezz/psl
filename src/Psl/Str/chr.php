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
 *
 * @psalm-pure
 */
function chr(int $ascii): string
{
    /** @var string|false $char */
    $char = \mb_chr($ascii, 'UTF-8');

    /** @psalm-suppress MissingThrowsDocblock */
    Psl\invariant(is_string($char), 'Unexpected Error.');

    return $char;
}
