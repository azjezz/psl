<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

/**
 * Perform case folding on a string.
 *
 * Example:
 *
 *      Str\fold('ẞ')
 *      => Str('ss')
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function fold(string $str, ?string $encoding = null): string
{
    foreach (Internal\CASE_FOLD as $k => $v) {
        $str = replace($str, $k, $v, $encoding);
    }

    return lowercase($str, $encoding);
}
