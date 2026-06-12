<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$native = new DateTimeImmutable('2025-03-15 12:00:00', new DateTimeZone('UTC'));
$dt = DateTime\DateTime::fromStdlib($native);
$back = $dt->toStdlib(); // DateTimeImmutable

IO\write_line('From stdlib: %s', $dt->toRfc3339());
IO\write_line('Back to stdlib: %s', $back->format('Y-m-d H:i:s T'));
