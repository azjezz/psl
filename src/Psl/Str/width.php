<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_strwidth;

/**
 * Return width of length.
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function width(string $str, ?string $encoding = null): int
{
    return mb_strwidth($str, Internal\internal_encoding($encoding));
}
