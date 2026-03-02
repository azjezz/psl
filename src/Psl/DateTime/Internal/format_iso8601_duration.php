<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\Math;
use Psl\Str;

/**
 * Formats a time-based duration as an ISO 8601 duration string.
 *
 * @param int $hours
 * @param int<-59, 59> $minutes
 * @param int<-59, 59> $seconds
 * @param int<-999999999, 999999999> $nanoseconds
 *
 * @internal
 *
 * @psalm-mutation-free
 */
function format_iso8601_duration(int $hours, int $minutes, int $seconds, int $nanoseconds): string
{
    if (0 === $hours && 0 === $minutes && 0 === $seconds && 0 === $nanoseconds) {
        return 'PT0S';
    }

    $negative = $hours < 0 || $minutes < 0 || $seconds < 0 || $nanoseconds < 0;
    $prefix = $negative ? '-PT' : 'PT';

    $hours = Math\abs($hours);
    $minutes = Math\abs($minutes);
    $seconds = Math\abs($seconds);
    $nanoseconds = Math\abs($nanoseconds);

    $result = $prefix;
    if ($hours > 0) {
        $result .= $hours . 'H';
    }

    if ($minutes > 0) {
        $result .= $minutes . 'M';
    }

    if ($seconds > 0 || $nanoseconds > 0) {
        $result .= $seconds;
        if ($nanoseconds > 0) {
            $frac = Str\pad_left((string) $nanoseconds, 9, '0');
            $frac = Str\trim_right($frac, '0');
            $result .= '.' . $frac;
        }

        $result .= 'S';
    }

    return $result;
}
