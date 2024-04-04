<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<mixed>
 */
function mixed(): TypeInterface
{
    /** @var Internal\MixedType $instance */
    static $instance = new Internal\MixedType();

    return $instance;
}
