<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<u16>
 *
 * @return TypeInterface<int<0, 65535>>
 */
function u16(): TypeInterface
{
    /** @var Internal\U16Type $instance */
    static $instance = new Internal\U16Type();

    return $instance;
}
