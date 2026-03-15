<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 */
function from(int $lowerBound): FromRange
{
    return new FromRange($lowerBound);
}
