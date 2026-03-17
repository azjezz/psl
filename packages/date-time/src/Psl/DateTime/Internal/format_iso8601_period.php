<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime\Exception;

use function abs;

/**
 * Formats a calendar-based period as an ISO 8601 duration string.
 *
 * @throws Exception\InvalidArgumentException If years/months and days have different signs.
 *
 * @internal
 *
 * @psalm-mutation-free
 */
function format_iso8601_period(int $years, int $months, int $days): string
{
    if (0 === $years && 0 === $months && 0 === $days) {
        return 'P0D';
    }

    // Determine overall sign. Years/months are sign-coherent due to normalization.
    // Days may differ in sign - ISO 8601 cannot represent mixed signs.
    $negative = $years < 0 || $months < 0 || $days < 0;
    $positive = $years > 0 || $months > 0 || $days > 0;

    if ($negative && $positive) {
        throw new Exception\InvalidArgumentException(
            'Cannot represent a Period with mixed-sign components in ISO 8601 format.',
        );
    }

    $prefix = $negative ? '-P' : 'P';

    $years = abs($years);
    $months = abs($months);
    $days = abs($days);

    $result = $prefix;
    if ($years > 0) {
        $result .= $years . 'Y';
    }

    if ($months > 0) {
        $result .= $months . 'M';
    }

    if ($days > 0) {
        $result .= $days . 'D';
    }

    return $result;
}
