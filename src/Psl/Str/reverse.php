<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Vec;

/**
 * Returns the given string reversed.
 *
 * @psalm-pure
 */
function reverse(string $string, Encoding $encoding = Encoding::UTF_8): string
{
        $chunks = chunk($string, encoding: $encoding);

        /** @psalm-suppress ImpureFunctionCall */
        return join(Vec\reverse($chunks), '');
}
