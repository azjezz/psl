<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<null>
 */
function null(): TypeInterface
{
    /** @var Internal\NullType $instance */
    static $instance = new Internal\NullType();

    return $instance;
}
