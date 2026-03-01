<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout\Internal;

use Psl\Math;
use Psl\Terminal\Layout\Constraint;

/**
 * @internal
 */
function resolve_max(Constraint $constraint, int $totalSpace): int
{
    /** @var Constraint $inner */
    $inner = $constraint->inner;
    $innerSize = resolve_constraint($inner, $totalSpace);
    if ($innerSize === -1) {
        return Math\minva($constraint->size, $totalSpace);
    }

    return Math\minva($constraint->size, $innerSize);
}
