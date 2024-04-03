<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<numeric-string>
 */
function numeric_string(): TypeInterface
{
    /** @var Internal\NumericStringType $instance */
    static $instance = new Internal\NumericStringType();

    return $instance;
}
