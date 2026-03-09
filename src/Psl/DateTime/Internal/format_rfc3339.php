<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime\SecondsStyle;
use Psl\DateTime\Timestamp;
use Psl\DateTime\Timezone;

use function str_pad;
use function str_replace;
use function substr;

use const STR_PAD_LEFT;

/**
 * @internal
 *
 * @psalm-mutation-free
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
    $fraction = substr(str_pad((string) $nanoseconds, 9, '0', STR_PAD_LEFT), 0, $seconds_style->value);
    if ('' !== $fraction) {
        $fraction = '.' . $fraction;
    }

    $pattern = match ($use_z) {
        true => 'yyyy-MM-dd\'T\'HH:mm:ss@ZZZZZ',
        false => 'yyyy-MM-dd\'T\'HH:mm:ss@xxx',
    };

    $formatter = namespace\create_intl_date_formatter(pattern: $pattern, timezone: $timezone);
    $rfc_string = $formatter->format($seconds);

    /** @var string */
    return str_replace('@', $fraction, $rfc_string);
}
