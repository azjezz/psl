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
    /** @var Internal\I64Type $instance */
    static $instance = new Internal\I64Type();

    return $instance;
}
