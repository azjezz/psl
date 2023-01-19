<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<f32>
 *
 * @return TypeInterface<float>
 */
function f32(): TypeInterface
{
    return new Internal\F32Type();
}
