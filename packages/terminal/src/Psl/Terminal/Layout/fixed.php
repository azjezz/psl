<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

/**
 * Create a fixed-size constraint.
 *
 * @pure
 *
 * @api
 */
function fixed(int $size): Constraint
{
    return Constraint::fixed($size);
}
