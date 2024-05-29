<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use IntlTimeZone;
use Psl;
use Psl\DateTime\Timezone;
use Psl\Str\Byte;

/**
 * @pure
 *
 * @internal
 *
 * @psalm-suppress ImpureMethodCall
 * @psalm-suppress MissingThrowsDocblock
 */
function to_intl_timezone(Timezone $timezone): IntlTimeZone
{
    $value = $timezone->value;
    if (Byte\starts_with($value, '+') || Byte\starts_with($value, '-')) {
        $value = 'GMT' . $value;
    }

    $tz = IntlTimeZone::createTimeZone($value);

    Psl\invariant(
        $tz !== null,
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
