<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<int>
 */
function int(): TypeInterface
{
    return new Internal\IntType();
}
