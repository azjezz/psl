<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

// An Interval is a range between two points in time
$start = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 1, 1);
$end = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 12, 31, 23, 59, 59);
$interval = DateTime\Interval::between($start, $end);

IO\write_line('Interval: %s', $interval->toString());

// Create from a start point and a duration
$hourInterval = DateTime\Interval::from(DateTime\Timestamp::now(), DateTime\Duration::hours(1));
IO\write_line('One hour from now: %s', $hourInterval->toString());

// Get the exact time duration between start and end
$duration = $interval->getDuration();
IO\write_line('Duration: %s', $duration->toString());

// Check if a point falls within the interval
$mid = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 6, 15);
$outside = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2026, 1, 1);

IO\write_line('Contains mid-year: %s', $interval->contains($mid) ? 'yes' : 'no');
IO\write_line('Contains next year: %s', $interval->contains($outside) ? 'yes' : 'no');

// Check if two intervals overlap and get their intersection
$q1 = DateTime\Interval::between(
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 1, 1),
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 3, 31),
);
$q2 = DateTime\Interval::between(
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 3, 1),
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 6, 30),
);

IO\write_line('Q1 overlaps Q2: %s', $q1->overlaps($q2) ? 'yes' : 'no');

$intersection = $q1->intersection($q2);
if (null !== $intersection) {
    IO\write_line('Intersection: %s', $intersection->toString());
}

// Check if one interval fully contains another
IO\write_line('Full year contains Q1: %s', $interval->containsInterval($q1) ? 'yes' : 'no');
IO\write_line('Q1 contains full year: %s', $q1->containsInterval($interval) ? 'yes' : 'no');

// Merge overlapping intervals
$merged = $q1->merge($q2);
IO\write_line('Merged Q1+Q2: %s', $merged->toString());

// Find the gap between non-overlapping intervals
$spring = DateTime\Interval::between(
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 3, 1),
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 5, 31),
);
$autumn = DateTime\Interval::between(
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 9, 1),
    DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, 11, 30),
);

$gap = $spring->gap($autumn);
if (null !== $gap) {
    IO\write_line('Gap between spring and autumn: %s', $gap->toString());
}

// ISO 8601 format
IO\write_line('ISO 8601: %s', $interval->toIso8601());
$parsed = DateTime\Interval::fromIso8601('2025-01-01T00:00:00Z/2025-12-31T23:59:59Z');
IO\write_line('Parsed: %s', $parsed->toString());
