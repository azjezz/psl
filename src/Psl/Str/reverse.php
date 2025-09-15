<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Vec;

/**
 * Returns the given string reversed.
 *
 * @pure
 */
function reverse(string $string, Encoding $encoding = Encoding::Utf8): string
{
    $chunks = namespace\chunk($string, encoding: $encoding);

    return namespace\join(Vec\reverse($chunks), '');
}
