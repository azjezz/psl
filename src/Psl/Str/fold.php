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
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function fold(string $string, ?string $encoding = null): string
{
    foreach (Internal\CASE_FOLD as $k => $v) {
        $string = replace($string, $k, $v, $encoding);
    }

    return lowercase($string, $encoding);
}
