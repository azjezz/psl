<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Vec;

/**
 * Returns the given string reversed.
 *
 * @psalm-pure
 */
function reverse(string $string, ?string $encoding = null): string
{
        $chunks = chunk($string, encoding: $encoding);
        return join(Vec\reverse($chunks), '');
}
