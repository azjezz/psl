<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use function abs;
use function rtrim;
use function str_pad;

use const STR_PAD_LEFT;

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

    $hours = abs($hours);
    $minutes = abs($minutes);
    $seconds = abs($seconds);
    $nanoseconds = abs($nanoseconds);

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
            $frac = str_pad((string) $nanoseconds, 9, '0', STR_PAD_LEFT);
            $frac = rtrim($frac, '0');
            $result .= '.' . $frac;
        }

        $result .= 'S';
    }

    return $result;
}
