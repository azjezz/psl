<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;
use Psl\IO;

// Convert a PSL DateTime to a PHP DateTimeImmutable
$psl = DateTime::fromParts(Timezone::AmericaNewYork, 2024, 6, 15, 14, 30, 45);
$stdlib = $psl->toStdlib();
IO\write_line('PSL to stdlib: %s', $stdlib->format('Y-m-d H:i:s T'));

// Convert back from a PHP DateTimeImmutable to a PSL DateTime
$back = DateTime::fromStdlib($stdlib);
IO\write_line('Back to PSL: %s', $back->toRfc3339());

// Convert a PSL Timezone to an IntlTimeZone
$intl = Timezone::AmericaNewYork->toIntl();
$id = $intl->getID();
IO\write_line('IntlTimeZone ID: %s', false === $id ? '<unknown>' : $id);

// Convert back from an IntlTimeZone to a PSL Timezone
$tz = Timezone::fromIntl($intl);
IO\write_line('Back to PSL Timezone: %s', $tz->value);
