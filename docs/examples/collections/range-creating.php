<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Range;

$range = Range\from(0); // 0..   (from 0 to infinity)
$range = Range\to(10); // ..10  (up to 10, exclusive)
$range = Range\to(10, true); // ..=10 (up to 10, inclusive)
$range = Range\between(0, 10); // 0..10 (from 0 to 10, exclusive)
$range = Range\between(0, 10, true); // 0..=10 (from 0 to 10, inclusive)
$range = Range\full(); // ..    (all integers)
