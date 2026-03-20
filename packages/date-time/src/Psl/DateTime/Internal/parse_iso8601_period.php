<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime;
use Psl\DateTime\Exception;

use function preg_match;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function substr;

/**
 * Parses an ISO 8601 date-based period string and returns its parts.
 *
 * @return array{int, int, int} [years, months, days]
 *
 * @throws Exception\ParserException If the string is not a valid ISO 8601 period.
 *
 * @internal
 *
 * @pure
 */
function parse_iso8601_period(string $value): array
{
    // Handle optional leading negative sign
    $negative = str_starts_with($value, '-');
    $input = $negative ? substr($value, 1) : $value;

    // Weeks format: P(\d+)W
    /** @var array<int, string> $matches */
    $matches = [];
    if (1 === preg_match('/^P(\d+)W$/', $input, $matches)) {
        $days = (int) $matches[1] * DateTime\DAYS_PER_WEEK;

        return [0, 0, $negative ? -$days : $days];
    }

    // Date-only format: P[(\d+)Y][(\d+)M][(\d+)D]
    /** @var array<int, string> $matches */
    $matches = [];
    if (1 !== preg_match('/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)D)?$/', $input, $matches)) {
        // Check for time component to give a better error message
        if (str_contains($input, 'T')) {
            throw new Exception\ParserException(sprintf(
                'ISO 8601 period "%s" contains time components; use Duration::fromIso8601() instead.',
                $value,
            ));
        }

        throw new Exception\ParserException(sprintf('Invalid ISO 8601 period "%s".', $value));
    }

    $years = (int) ($matches[1] ?? 0);
    $months = (int) ($matches[2] ?? 0);
    $days = (int) ($matches[3] ?? 0);

    if ($negative) {
        $years = -$years;
        $months = -$months;
        $days = -$days;
    }

    return [$years, $months, $days];
}
