<?php

declare(strict_types=1);

namespace Psl\Str;

function encoding(string $str): string
{
    return \mb_detect_encoding($str, mb_detect_order(), true) ?: 'UTF-8';
}
