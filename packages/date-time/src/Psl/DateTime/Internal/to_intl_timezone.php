<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use IntlTimeZone;
use Psl;
use Psl\DateTime\Timezone;

use function str_starts_with;

/**
 * @pure
 *
 * @internal
 */
function to_intl_timezone(Timezone $timezone): IntlTimeZone
{
    $value = $timezone->value;
    if (str_starts_with($value, '+') || str_starts_with($value, '-')) {
        $value = 'GMT' . $value;
    }

    $tz = IntlTimeZone::createTimeZone($value);

    Psl\invariant(
        null !== $tz,
        'Failed to create intl timezone from timezone "%s" ( "%s" / "%s" ).',
        $timezone->name,
        $timezone->value,
        $value,
    );

    Psl\invariant(
        $tz->getID() !== 'Etc/Unknown' || $tz->getRawOffset() !== 0,
        'Failed to create a valid intl timezone, unknown timezone "%s" ( "%s" / "%s" ) given.',
        $timezone->name,
        $timezone->value,
        $value,
    );

    return $tz;
}
