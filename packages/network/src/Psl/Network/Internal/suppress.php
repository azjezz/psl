<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Closure;

use function error_reporting;

/**
 * @param (Closure(): T) $fun
 *
 * @return T
 *
 * @internal
 */
function suppress<T>(Closure $fun): T
{
    $previousLevel = error_reporting(0);

    try {
        return $fun();
    } finally {
        error_reporting($previousLevel);
    }
}
