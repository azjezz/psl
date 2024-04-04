<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<u8>
 *
 * @return TypeInterface<int<0, 255>>
 */
function u8(): TypeInterface
{
    /** @var Internal\U8Type $instance */
    static $instance = new Internal\U8Type();

    return $instance;
}
