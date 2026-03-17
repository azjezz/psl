<?php

declare(strict_types=1);

namespace Psl\Str;

use function vsprintf;

/**
 * Return a formatted string.
 *
 * Examples:
 *
 *      Str\format('Hello, %s', 'azjezz')
 *      => Str('Hello, azjezz')
 *
 *      Str\format('%s is %d character(s) long.', 'س', Str\length('س'));
 *      => Str('س is 1 character(s) long.')
 *
 * @param int|float|string ...$args
 *
 * @pure
 *
 * @return string a string produced according to the $format string.
 */
function format(string $format, mixed ...$args): string
{
    return $args === [] ? $format : vsprintf($format, $args);
}
