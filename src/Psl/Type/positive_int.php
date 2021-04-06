<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<positive-int>
 */
function positive_int(): TypeInterface
{
    return new Internal\PositiveIntType();
}
