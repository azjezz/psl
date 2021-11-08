<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

use function mb_ord;

/**
 * Get code point of character.
 *
 * Example:
 *
 *      Str\ord('H')
 *      => Int(72)
 *
 *      Str\ord('ل')
 *      => Int(1604)
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function ord(string $character, Encoding $encoding = Encoding::UTF_8): int
{
    /**
     * @psalm-suppress UndefinedPropertyFetch
     * @psalm-suppress MixedArgument
     */
    return mb_ord($character, $encoding->value);
}
