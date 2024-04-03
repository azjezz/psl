<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<array-key>
 */
function array_key(): TypeInterface
{
    /** @var Internal\ArrayKeyType $instance */
    static $instance = new Internal\ArrayKeyType();

    return $instance;
}
