<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Closure;
use Psl\DateTime\Timezone;

use function date_default_timezone_get;
use function date_default_timezone_set;

/**
 * @template T
 *
 * @param (Closure(): T) $callback
 *
 * @return T
 *
 * @internal
 *
 * @pure - This technically a lie.
 */
function zone_override(?Timezone $tz, Closure $callback): mixed
{
    $original = null;
    try {
        if ($tz !== null) {
            $original = date_default_timezone_get();
            date_default_timezone_set($tz->value);
        }

        return $callback();
    } finally {
        if ($original !==  null) {
            date_default_timezone_set($original);
        }
    }
}
