<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<i16>
 *
 * @return TypeInterface<int<-32768, 32767>>
 */
function i16(): TypeInterface
{
    /** @var Internal\I16Type $instance */
    static $instance = new Internal\I16Type();

    return $instance;
}
