<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$a = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, DateTime\Month::January, 1);
$b = DateTime\DateTime::fromParts(DateTime\Timezone::UTC, 2025, DateTime\Month::June, 15);

IO\write_line('a before b: %s', $a->before($b) ? 'yes' : 'no'); // true
IO\write_line('b after a: %s', $b->after($a) ? 'yes' : 'no'); // true
IO\write_line('a same time as a: %s', $a->atTheSameTime($a) ? 'yes' : 'no'); // true
IO\write_line('a between a and b: %s', $a->betweenTimeInclusive($a, $b) ? 'yes' : 'no'); // true

// Inspect components
IO\write_line('Year: %d', $a->getYear());
IO\write_line('Month: %s', $a->getMonthEnum()->name);
IO\write_line('Weekday: %s', $a->getWeekday()->name);
IO\write_line('Leap year: %s', $a->isLeapYear() ? 'yes' : 'no');
IO\write_line('DST: %s', $a->isDaylightSavingTime() ? 'yes' : 'no');
