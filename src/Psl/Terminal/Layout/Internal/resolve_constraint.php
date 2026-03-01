<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout\Internal;

use Psl\Math;
use Psl\Terminal\Layout\Constraint;
use Psl\Terminal\Layout\ConstraintKind;

/**
 * Resolve a constraint to a concrete size, or -1 for fill.
 *
 * @internal
 */
function resolve_constraint(Constraint $constraint, int $totalSpace): int
{
    return match ($constraint->kind) {
        ConstraintKind::Fill => -1,
        ConstraintKind::Fixed => Math\minva($constraint->size, $totalSpace),
        ConstraintKind::Min => resolve_min($constraint, $totalSpace),
        ConstraintKind::Max => resolve_max($constraint, $totalSpace),
    };
}
