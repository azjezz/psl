<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 */
function to(int $upperBound, bool $inclusive = false): ToRange
{
    return new ToRange($upperBound, $inclusive);
}
