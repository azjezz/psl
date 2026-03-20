<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout\Internal;

use Psl\Terminal\Layout\Constraint;

use function min;

/**
 * @internal
 */
function resolve_max(Constraint $constraint, int $totalSpace): int
{
    /** @var Constraint $inner */
    $inner = $constraint->inner;
    $innerSize = namespace\resolve_constraint($inner, $totalSpace);
    if ($innerSize === -1) {
        return min($constraint->size, $totalSpace);
    }

    return min($constraint->size, $innerSize);
}
