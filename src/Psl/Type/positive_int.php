<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<positive-int>
 *
 * @deprecated use {@see uint} instead.
 */
function positive_int(): TypeInterface
{
    return new Internal\PositiveIntType();
}
