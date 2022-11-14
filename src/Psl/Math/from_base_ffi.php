<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Internal\ForeignFunctionInterface;

function from_base_ffi(string $number, int $base): int
{
    $binding = ForeignFunctionInterface\MathBinding::get();
    if (null === $binding) {
        exit("ffi is disblaed, here we can fallback to php implementation, or maybe wasm binding");
    }

    return $binding->from_base($number, $base);
}
