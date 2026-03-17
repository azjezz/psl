<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

/**
 * Create a fixed-size constraint.
 *
 * @pure
 */
function fixed(int $size): Constraint
{
    return Constraint::fixed($size);
}
