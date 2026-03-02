<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

// Create periods from calendar units
$oneYear = DateTime\Period::years(1);
$sixMonths = DateTime\Period::months(6);
$twoWeeks = DateTime\Period::weeks(2); // 14 days
$tenDays = DateTime\Period::days(10);

// Months automatically normalize into years
$period = DateTime\Period::months(14); // 1 year, 2 months
IO\write_line('14 months = %s', $period->toString());

// Days are never normalized (a day is not a fixed number of hours)
$longPeriod = DateTime\Period::days(365);
IO\write_line('365 days = %s', $longPeriod->toString());

// Combine parts
$mixed = DateTime\Period::fromParts(1, 6, 15);
IO\write_line('Mixed: %s', $mixed->toString());

// Arithmetic
$a = DateTime\Period::fromParts(1, 6, 0);
$b = DateTime\Period::fromParts(0, 8, 15);
IO\write_line('Sum: %s', $a->plus($b)->toString());
IO\write_line('Difference: %s', $a->minus($b)->toString());
IO\write_line('Inverted: %s', $a->invert()->toString());

// Calculate the period between two dates
$start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2024, 3, 15);
$end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 7, 20);
$between = DateTime\Period::between($start, $end);
IO\write_line('Between: %s', $between->toString()); // 1 year(s), 4 month(s), 5 day(s)

// ISO 8601 format
IO\write_line('ISO 8601: %s', $mixed->toIso8601()); // P1Y6M15D
$parsed = DateTime\Period::fromIso8601('P2Y3M10D');
IO\write_line('Parsed: %s', $parsed->toString());
