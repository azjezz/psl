<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Return a formatted string.
 *
 * The format string is composed of zero or more directives:
 * ordinary characters (excluding %) that are copied directly to the result,
 * and conversion specifications, each of which results in fetching its
 * own parameter.
 *
 * Each conversion specification consists of a percent sign (%), followed by one or more of these
 * elements, in order:
 * An optional sign specifier that forces a sign (- or +) to be used on a number.
 * By default, only the - sign is used on a number if it's negative.
 * This specifier forces positive numbers to have the + sign attached as well.
 *
 * Examples:
 *
 *      Str\format('Hello, %s', 'azjezz')
 *      => Str('Hello, azjezz')
 *
 *      Str\format('%s is %d character(s) long.', 'س', Str\length('س'));
 *      => Str('س is 1 character(s) long.')
 *
 * @psalm-param int|float|string  ...$args
 *
 * @return string a string produced according to the formatting string
 *                format
 */
function format(string $format, ...$args): string
{
    return \vsprintf($format, $args);
}
