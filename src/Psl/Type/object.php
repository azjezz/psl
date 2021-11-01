<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<object>
 */
function object(): TypeInterface
{
    return new Internal\ObjectType();
}
