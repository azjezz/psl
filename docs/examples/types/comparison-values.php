<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Comparison;

Comparison\compare(1, 2); // Order::Less
Comparison\compare('b', 'a'); // Order::Greater
Comparison\equal(42, 42); // true
Comparison\less(1, 2); // true
Comparison\greater_or_equal(3, 3); // true
