<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

use function mb_strwidth;

/**
 * Return width of length.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function width(string $string, Encoding $encoding = Encoding::UTF_8): int
{
    /**
     * @psalm-suppress UndefinedPropertyFetch
     * @psalm-suppress MixedArgument
     */
    return mb_strwidth($string, $encoding->value);
}
