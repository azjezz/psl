<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @return TypeInterface<int>
 *
 * @api
 */
function int_range(int $min, int $max): TypeInterface<int>
{
    return new Internal\IntRangeType($min, $max);
}
