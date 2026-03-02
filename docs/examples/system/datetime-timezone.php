<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$utc = DateTime\DateTime::now(DateTime\Timezone::UTC);
$la = $utc->convertToTimezone(DateTime\Timezone::AmericaLosAngeles);

// Same moment, different local time
IO\write_line('UTC: %s', $utc->toRfc3339());
IO\write_line('LA:  %s', $la->toRfc3339());
IO\write_line('Same time: %s', $utc->atTheSameTime($la) ? 'yes' : 'no');
