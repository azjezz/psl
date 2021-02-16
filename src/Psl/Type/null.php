<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return TypeInterface<null>
 */
function null(): TypeInterface
{
    return new Internal\NullType();
}
