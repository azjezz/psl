<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Concat all given strings into one.
 *
 * Example:
 *
 *      Str\concat('a', 'b', 'c');
 *      => Str('abc')
 *
 *      Str\concat('foo', ...['a', 'b']);
 *      => Str('fooab')
 *
 * @pure
 */
function concat(string $string, string ...$rest): string
{
    foreach ($rest as $str) {
        $string .= $str;
    }

    return $string;
}
