<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<int>
 */
function int_range(int $min, int $max): TypeInterface
{
    return new Internal\IntRangeType($min, $max);
}
