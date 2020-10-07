<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_chr;

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
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function chr(int $codepoint, ?string $encoding = null): string
{
    /** @var string|false $char */
    $char = mb_chr($codepoint, Internal\internal_encoding($encoding));

    /** @psalm-suppress MissingThrowsDocblock */
    Psl\invariant(is_string($char), 'Unexpected Error.');

    return $char;
}
