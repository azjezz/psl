<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime\Exception;
use Psl\Str;

use function preg_match;

/**
 * Parses an ISO 8601 time-based duration string and returns its parts.
 *
 * @return array{int, int, int, int} [hours, minutes, seconds, nanoseconds]
 *
 * @throws Exception\ParserException If the string is not a valid ISO 8601 duration.
 *
 * @internal
 *
 * @pure
 */
function parse_iso8601_duration(string $value): array
{
    // Handle optional leading negative sign
    $negative = Str\starts_with($value, '-');
    $input = $negative ? Str\slice($value, 1) : $value;

    // Time-only format: PT[(\d+)H][(\d+)M][(\d+[.\d+]?)S]
    /** @var array<int, string> $matches */
    $matches = [];
    if (1 !== preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?$/', $input, $matches)) {
        // Check for date component to give a better error message
        if (Str\starts_with($input, 'P') && !Str\starts_with($input, 'PT')) {
            throw new Exception\ParserException(Str\format(
                'ISO 8601 duration "%s" contains date components; use Period::fromIso8601() instead.',
                $value,
            ));
        }

        throw new Exception\ParserException(Str\format('Invalid ISO 8601 duration "%s".', $value));
    }

    $hours = (int) ($matches[1] ?? 0);
    $minutes = (int) ($matches[2] ?? 0);
    $seconds = 0;
    $nanoseconds = 0;

    $secStr = $matches[3] ?? '';
    if ('' !== $secStr) {
        if (Str\contains($secStr, '.')) {
            $parts = Str\split($secStr, '.');
            $seconds = (int) $parts[0];
            $frac = Str\pad_right($parts[1], 9, '0');
            $nanoseconds = (int) Str\slice($frac, 0, 9);
        } else {
            $seconds = (int) $secStr;
        }
    }

    if ($negative) {
        $hours = -$hours;
        $minutes = -$minutes;
        $seconds = -$seconds;
        $nanoseconds = -$nanoseconds;
    }

    return [$hours, $minutes, $seconds, $nanoseconds];
}
