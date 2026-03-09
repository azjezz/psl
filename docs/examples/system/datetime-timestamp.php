<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$ts = DateTime\Timestamp::now();
IO\write_line('Seconds since epoch: %d', $ts->getSeconds());
IO\write_line('Nanoseconds: %d', $ts->getNanoseconds());

$specific = DateTime\Timestamp::fromParts(1_700_000_000, 500_000_000);

// Create from milliseconds or microseconds
$fromMs = DateTime\Timestamp::fromMilliseconds(1_700_000_000_500);
$fromUs = DateTime\Timestamp::fromMicroseconds(1_700_000_000_500_000);

$dt = DateTime\DateTime::fromTimestamp($ts, DateTime\Timezone::UTC);
IO\write_line('From timestamp: %s', $dt->toRfc3339());
