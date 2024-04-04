<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<float>
 */
function float(): TypeInterface
{
    /** @var Internal\FloatType $instance */
    static $instance = new Internal\FloatType();

    return $instance;
}
