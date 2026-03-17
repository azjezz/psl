<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

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
function suppress(Closure $fun): mixed
{
    $previousLevel = error_reporting(0);

    try {
        return $fun();
    } finally {
        error_reporting($previousLevel);
    }
}
