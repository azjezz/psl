<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

/**
 * Create a minimum-size constraint wrapping another constraint.
 *
 * @pure
 */
function min(int $min, Constraint $constraint): Constraint
{
    return Constraint::min($min, $constraint);
}
