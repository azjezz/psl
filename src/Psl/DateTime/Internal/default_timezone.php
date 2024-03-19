<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime\Timezone;

use function date_default_timezone_get;

/**
 * @internal
 *
 * @psalm-mutation-free
 *
 * @psalm-suppress ImpureFunctionCall - `date_default_timezone_get()` is mutation free, as it performs a read-only operation.
 */
function default_timezone(): Timezone
{
    /**
     * `date_default_timezone_get` function might return any of the "Others" timezones
     * mentioned in PHP doc: https://www.php.net/manual/en/timezones.others.php.
     *
     * those timezones are not supported by Psl ( aside from UTC ), as they are considered "legacy".
     */
    $timezone_id = date_default_timezone_get();

    return Timezone::tryFrom($timezone_id) ?? Timezone::UTC;
}
