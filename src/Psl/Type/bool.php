<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<bool>
 */
function bool(): TypeInterface
{
    /** @var Internal\BoolType $instance */
    static $instance = new Internal\BoolType();

    return $instance;
}
