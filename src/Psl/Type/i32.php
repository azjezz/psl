<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<i32>
 *
 * @return TypeInterface<int<-2147483648, 2147483647>>
 */
function i32(): TypeInterface
{
    return new Internal\I32Type();
}
