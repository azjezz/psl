<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 */
function from(int $lower_bound): FromRange
{
    return new FromRange($lower_bound);
}
