<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<null>
 */
function null(): TypeInterface
{
    return new Internal\NullType();
}
