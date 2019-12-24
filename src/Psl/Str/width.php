<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Return width of string.
 */
function width(string $str): int
{
    return \mb_strwidth($str, encoding($str));
}
