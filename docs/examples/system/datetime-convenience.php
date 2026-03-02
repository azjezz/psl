<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$dt = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 6, 15, 14, 30, 45);

// Start and end of day
$startOfDay = $dt->atStartOfDay();
IO\write_line('Start of day: %s', $startOfDay->toRfc3339()); // 2025-06-15T00:00:00+00:00

$endOfDay = $dt->atEndOfDay();
IO\write_line('End of day: %s', $endOfDay->toRfc3339()); // 2025-06-15T23:59:59.999999999+00:00

// Start and end of month
$startOfMonth = $dt->atStartOfMonth();
IO\write_line('Start of month: %s', $startOfMonth->toRfc3339()); // 2025-06-01T00:00:00+00:00

$endOfMonth = $dt->atEndOfMonth();
IO\write_line('End of month: %s', $endOfMonth->toRfc3339()); // 2025-06-30T23:59:59.999999999+00:00

// Start and end of year
$startOfYear = $dt->atStartOfYear();
IO\write_line('Start of year: %s', $startOfYear->toRfc3339()); // 2025-01-01T00:00:00+00:00

$endOfYear = $dt->atEndOfYear();
IO\write_line('End of year: %s', $endOfYear->toRfc3339()); // 2025-12-31T23:59:59.999999999+00:00

// Start and end of week (Monday-Sunday)
$startOfWeek = $dt->atStartOfWeek();
IO\write_line('Start of week: %s', $startOfWeek->toRfc3339()); // 2025-06-09T00:00:00+00:00

$endOfWeek = $dt->atEndOfWeek();
IO\write_line('End of week: %s', $endOfWeek->toRfc3339()); // 2025-06-15T23:59:59.999999999+00:00

// Useful for date range queries
$feb = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 2, 15);
IO\write_line('Feb 2024 ends: day %d', $feb->atEndOfMonth()->getDay()); // 29 (leap year)
