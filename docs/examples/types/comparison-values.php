<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Comparison;

Comparison\compare::<int>(1, 2); // Order::Less
Comparison\compare::<string>('b', 'a'); // Order::Greater
Comparison\equal::<int>(42, 42); // true
Comparison\less::<int>(1, 2); // true
Comparison\greater_or_equal::<int>(3, 3); // true
