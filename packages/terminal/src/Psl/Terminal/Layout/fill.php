<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

/**
 * Create a fill constraint that takes all remaining space.
 *
 * @pure
 *
 * @api
 */
function fill(): Constraint
{
    return Constraint::fill();
}
