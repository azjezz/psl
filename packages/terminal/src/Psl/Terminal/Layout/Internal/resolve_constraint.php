<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout\Internal;

use Psl\Terminal\Layout\Constraint;
use Psl\Terminal\Layout\ConstraintKind;

use function min;

/**
 * Resolve a constraint to a concrete size, or -1 for fill.
 *
 * @internal
 */
function resolve_constraint(Constraint $constraint, int $totalSpace): int
{
    return match ($constraint->kind) {
        ConstraintKind::Fill => -1,
        ConstraintKind::Fixed => min($constraint->size, $totalSpace),
        ConstraintKind::Min => namespace\resolve_min($constraint, $totalSpace),
        ConstraintKind::Max => namespace\resolve_max($constraint, $totalSpace),
    };
}
