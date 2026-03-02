<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$twoHours = DateTime\Duration::hours(2);
$timeout = DateTime\Duration::seconds(30);
$precise = DateTime\Duration::milliseconds(1500); // normalizes to 1s 500ms

$now = DateTime\DateTime::now(DateTime\Timezone::UTC);

// Add or subtract durations from dates
$later = $now->plus(DateTime\Duration::hours(3));
$earlier = $now->minus(DateTime\Duration::days(7));

// Convenience methods
$tomorrow = $now->plusDays(1);
$nextMonth = $now->plusMonths(1);
$lastYear = $now->minusYears(1);

IO\write_line('Now: %s', $now->toRfc3339());
IO\write_line('3 hours later: %s', $later->toRfc3339());
IO\write_line('7 days earlier: %s', $earlier->toRfc3339());
IO\write_line('Tomorrow: %s', $tomorrow->toRfc3339());
IO\write_line('Next month: %s', $nextMonth->toRfc3339());
IO\write_line('Last year: %s', $lastYear->toRfc3339());

// Measure elapsed time
$start = DateTime\Timestamp::now();
$end = DateTime\Timestamp::now();
$elapsed = $end->since($start); // returns Duration
IO\write_line('Elapsed: %f seconds', $elapsed->getTotalSeconds());
