<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * @psalm-pure
 */
function encoding(string $str): string
{
    return \mb_detect_encoding($str, null, true) ?: 'UTF-8';
}
