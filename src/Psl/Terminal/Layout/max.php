<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

/**
 * Create a maximum-size constraint wrapping another constraint.
 *
 * @pure
 */
function max(int $max, Constraint $constraint): Constraint
{
    return Constraint::max($max, $constraint);
}
