<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<i64>
 *
 * @return TypeInterface<int>
 */
function i64(): TypeInterface
{
    return new Internal\I64Type();
}
