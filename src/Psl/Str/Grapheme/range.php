<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl\Range\LowerBoundRangeInterface;
use Psl\Range\RangeInterface;
use Psl\Range\UpperBoundRangeInterface;
use Psl\Str\Exception;

/**
 * Slice a string using a range.
 *
 * If the range doesn't have an upper range, the slice will contain the
 * rest of the string. If the upper-bound is equal to the lower-bound,
 * then an empty string will be returned.
 *
 * Example:
 *
 * ```php
 * use Psl\Range;
 * use Psl\Str;
 *
 * $string = 'Hello, World!';
 *
 * Str\range($string, Range\between(0, 3, upper_inclusive: true)); // 'Hell'
 * Str\range($string, Range\between(0, 3, upper_inclusive: false)); // 'Hel'
 * Str\range($string, Range\from(3)); // 'lo, World!'
 * Str\range($string, Range\to(3, true)); // 'Hell'
 * Str\range($string, Range\to(3, false)); // 'Hel'
 * Str\range($string, Range\full()); // 'Hello, World!'
 * Str\range($string, Range\between(7, 5, true)); // 'World'
 * ```
 *
 * @param RangeInterface $range
 *
 * @throws Exception\OutOfBoundsException If the $offset is out-of-bounds.
 * @throws Exception\InvalidArgumentException If $string is not made of grapheme clusters.
 *
 * @pure
 */
function range(string $string, RangeInterface $range): string
{
    $offset = 0;
    $length = null;
    if ($range instanceof LowerBoundRangeInterface) {
        /** @var int<0, max> $offset */
        $offset = $range->getLowerBound();
    }
    
    if ($range instanceof UpperBoundRangeInterface) {
        /** @var int<0, max> $length */
        $length = $range->getUpperBound() - $offset;
        if ($range->isUpperInclusive()) {
            $length += 1;
        }
    }

    return slice($string, $offset, $length);
}
