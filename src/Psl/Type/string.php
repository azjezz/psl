<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<string>
 */
function string(): TypeInterface
{
    /** @var Internal\StringType $instance */
    static $instance = new Internal\StringType();

    return $instance;
}
