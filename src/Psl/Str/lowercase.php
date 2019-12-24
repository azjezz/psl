<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the string with all alphabetic characters converted to lowercase.
 *
 * Example:
 *      Str\lowercase('hello')
 *      => Str('hello')
 *
 *      Str\lowercase('Hello')
 *      => Str('hello')
 *
 *      Str\lowercase('HELLO')
 *      => Str('hello')
 *
 *      Str\lowercase('1337')
 *      => Str('1337')
 *
 *      Str\contains('سيف')
 *      => Str('سيف')
 */
function lowercase(string $lowercase): string
{
    return \mb_strtolower($lowercase, encoding($lowercase));
}
