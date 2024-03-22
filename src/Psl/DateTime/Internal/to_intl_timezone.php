<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use IntlTimeZone;
use Psl\DateTime\Timezone;
use Psl\Str\Byte;

/**
 * @pure
 *
 * @internal
 */
function to_intl_timezone(Timezone $timezone): IntlTimeZone
{
    $value = $timezone->value;
    if (Byte\starts_with($value, '+') || Byte\starts_with($value, '-')) {
        $value = 'GMT' . $value;
    }

    return IntlTimeZone::createTimeZone($value);
}
