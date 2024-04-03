<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<object>
 */
function object(): TypeInterface
{
    /** @var Internal\ObjectType $instance */
    static $instance = new Internal\ObjectType();

    return $instance;
}
