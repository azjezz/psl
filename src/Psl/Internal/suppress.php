<?php

declare(strict_types=1);

namespace Psl\Internal;

use Closure;

use function error_reporting;

/**
 * @template T
 *
 * @param (Closure(): T) $fun
 *
 * @return T
 *
 * @internal
 */
function suppress(Closure $fun)
{
    $previous_level = error_reporting(0);

    try {
        return $fun();
    } finally {
        error_reporting($previous_level);
    }
}
