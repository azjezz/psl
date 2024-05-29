<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_strlen;

/**
 * Returns the length of the given string, i.e. the number of bytes.
 *
 * Example:
 *
 *      Str\length('Hello')
 *      => Int(5)
 *
 *      Str\length('سيف')
 *      => Int(3)
 *
 *      Str\length('تونس')
 *      => Int(4)
 *
 * @return int<0, max>
 *
 * @pure
 */
function length(string $string, Encoding $encoding = Encoding::Utf8): int
{
    /** @var int<0, max> */
    return mb_strlen($string, $encoding->value);
}
