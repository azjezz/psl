<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime\SecondsStyle;
use Psl\DateTime\Timestamp;
use Psl\DateTime\Timezone;
use Psl\Str\Byte;

/**
 * @internal
 *
 * @psalm-mutation-free
 *
 * @psalm-suppress ImpureMethodCall
 * @mago-ignore best-practices/no-else-clause
 */
function format_rfc3339(
    Timestamp $timestamp,
    null|SecondsStyle $seconds_style = null,
    bool $use_z = false,
    null|Timezone $timezone = null,
): string {
    $seconds_style ??= SecondsStyle::fromTimestamp($timestamp);

    if (null === $timezone) {
        $timezone = Timezone::UTC;
    } elseif ($use_z) {
        $use_z = Timezone::UTC === $timezone;
    }

    $seconds = $timestamp->getSeconds();
    $nanoseconds = $timestamp->getNanoseconds();

    // Intl formatter cannot handle nanoseconds and microseconds, do it manually instead.
    $fraction = Byte\slice((string) $nanoseconds, 0, $seconds_style->value);
    if ($fraction !== '') {
        $fraction = '.' . $fraction;
    }

    $pattern = match ($use_z) {
        true => 'yyyy-MM-dd\'T\'HH:mm:ss@ZZZZZ',
        false => 'yyyy-MM-dd\'T\'HH:mm:ss@xxx',
    };

    $formatter = namespace\create_intl_date_formatter(pattern: $pattern, timezone: $timezone);
    $rfc_string = $formatter->format($seconds);

    return Byte\replace($rfc_string, '@', $fraction);
}
