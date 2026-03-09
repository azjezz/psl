<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

// int() coerces from int, numeric strings, and floats with .00 decimal
Type\int()->coerce('42'); // 42
Type\int()->coerce(42.0); // 42

// string() coerces from string, int, and Stringable objects
Type\string()->coerce(42); // '42'

// bool() coerces from bool, 0/1 (int), and '0'/'1' (string)
Type\bool()->coerce(1); // true
