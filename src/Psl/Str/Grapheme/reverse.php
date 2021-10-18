<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Exception\InvalidArgumentException;

use function grapheme_strlen;
use function grapheme_substr;

/**
 * Reverses the string.
 *
 * @pure
 *
 * @throws \Psl\Exception\InvalidArgumentException If $string length cannot be determined.
 */
function reverse(string $string): string
{
    $reversed = '';

    $length = grapheme_strlen($string);

    if ($length === false || $length === null) {
        throw new InvalidArgumentException('Cannot get string length. Possibly invalid UTF-8 sequence');
    }

    while ($length-- > 0) {
        $reversed .= grapheme_substr($string, $length, 1);
    }

    return $reversed;
}
