<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout\Internal;

use Psl\Terminal\Layout\Constraint;

use function max;

/**
 * @internal
 */
function resolve_min(Constraint $constraint, int $totalSpace): int
{
    /** @var Constraint $inner */
    $inner = $constraint->inner;
    $innerSize = namespace\resolve_constraint($inner, $totalSpace);
    if ($innerSize === -1) {
        return -1;
    }

    return max($constraint->size, $innerSize);
}
