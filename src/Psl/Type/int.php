<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<int>
 */
function int(): TypeInterface
{
    /** @var Internal\IntType $instance */
    static $instance = new Internal\IntType();

    return $instance;
}
