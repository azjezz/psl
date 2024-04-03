<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @ara-return TypeInterface<nonnull>
 *
 * @return TypeInterface<mixed>
 */
function nonnull(): TypeInterface
{
    /** @var Internal\NonNullType $instance */
    static $instance = new Internal\NonNullType();

    return $instance;
}
