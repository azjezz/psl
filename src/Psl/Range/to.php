<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 */
function to(int $upper_bound, bool $inclusive = false): ToRange
{
    return new ToRange($upper_bound, $inclusive);
}
