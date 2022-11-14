<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Internal\ForeignFunctionInterface;

function to_base_ffi(int $number, int $base): string
{
    $binding = ForeignFunctionInterface\MathBinding::get();
    if (null === $binding) {
        exit("ffi is disblaed, here we can fallback to php implementation, or maybe wasm binding");
    }

    return $binding->to_base($number, $base);
}
