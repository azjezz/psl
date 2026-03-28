<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 *
 * @api
 */
function from(int $lowerBound): FromRange
{
    return new FromRange($lowerBound);
}
