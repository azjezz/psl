<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<f64>
 *
 * @return TypeInterface<float>
 */
function f64(): TypeInterface
{
    return new Internal\F64Type();
}
