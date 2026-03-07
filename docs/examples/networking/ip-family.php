<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IP\Family;

$family = Family::V4;
$family->value; // 4 (byte size)
$family->ianaFamily(); // 1

$fromIana = Family::fromIanaFamily(2);
$fromIana === Family::V6; // true
