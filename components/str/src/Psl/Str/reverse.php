<?php

declare(strict_types=1);

namespace Psl\Str;

use function array_reverse;

/**
 * Returns the given string reversed.
 *
 * @pure
 */
function reverse(string $string, Encoding $encoding = Encoding::Utf8): string
{
    $chunks = namespace\chunk($string, encoding: $encoding);

    return namespace\join(array_reverse($chunks), '');
}
