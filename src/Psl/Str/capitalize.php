<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the string with the first character capitalized.
 *
 * If the first character is already capitalized or isn't alphabetic, the string
 * will be unchanged.
 *
 * Example:
 *
 *      Str\capitalize('hello')
 *      => Str('Hello')
 *
 *      Str\capitalize('Hello')
 *      => Str('Hello')
 *
 *      Str\capitalize('مرحبا بكم')
 *      => Str('مرحبا بكم')
 *
 *      Str\capitalize('1337')
 *      => Str('1337)
 *
 * @pure
 */
function capitalize(string $string, Encoding $encoding = Encoding::UTF_8): string
{
    if ('' === $string) {
        return '';
    }

    return concat(
        uppercase(slice($string, 0, 1, $encoding), $encoding),
        slice($string, 1, length($string, $encoding), $encoding)
    );
}
