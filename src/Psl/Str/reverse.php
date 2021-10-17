<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Vec;

/**
 * Returns the given string reversed.
 */
function reverse(string $string, ?string $encoding = null): string
{
        $parts = split($string, '', null, $encoding);
        return join(Vec\reverse($parts), '');
}
