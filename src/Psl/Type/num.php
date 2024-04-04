<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<int|float>
 */
function num(): TypeInterface
{
    /** @var Internal\NumType $instance */
    static $instance = new Internal\NumType();

    return $instance;
}
