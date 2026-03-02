<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$now = DateTime\DateTime::now();
IO\write_line('Now: %s', $now->toRfc3339());

$tokyo = DateTime\DateTime::now(DateTime\Timezone::AsiaTokyo);
IO\write_line('Tokyo: %s', $tokyo->toRfc3339());

// From individual components
$birthday = DateTime\DateTime::fromParts(DateTime\Timezone::AmericaNewYork, 1990, DateTime\Month::March, 15, 14, 30, 0);
IO\write_line('Birthday: %s', $birthday->toRfc3339());

// A specific time today
$meeting = DateTime\DateTime::todayAt(9, 0, timezone: DateTime\Timezone::EuropeLondon);
IO\write_line('Meeting: %s', $meeting->toRfc3339());
