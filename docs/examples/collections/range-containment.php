<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Range;

$range = Range\between(0, 10);
$range->contains(5); // true
$range->contains(100); // false

$range = Range\from(0);
$range->contains(5); // true
$range->contains(100); // true

$range = Range\full();
$range->contains(5); // true
$range->contains(100); // true
