<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout\Internal;

use Psl\Math;
use Psl\Terminal\Layout\Constraint;

/**
 * @internal
 */
function resolve_min(Constraint $constraint, int $totalSpace): int
{
    /** @var Constraint $inner */
    $inner = $constraint->inner;
    $innerSize = resolve_constraint($inner, $totalSpace);
    if ($innerSize === -1) {
        return -1;
    }

    return Math\maxva($constraint->size, $innerSize);
}
