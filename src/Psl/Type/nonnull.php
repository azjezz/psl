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
    return new Internal\NonNullType();
}
