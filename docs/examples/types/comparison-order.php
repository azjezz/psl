<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Comparison\Order;
use Psl\IO;

// Order replaces magic integers (-1, 0, 1) with readable, type-safe cases
IO\write_error_line('Order::Less    = %d', Order::Less->value); // -1 - first value is smaller
IO\write_error_line('Order::Equal   = %d', Order::Equal->value); // 0  - values are equal
IO\write_error_line('Order::Greater = %d', Order::Greater->value); // 1  - first value is larger
