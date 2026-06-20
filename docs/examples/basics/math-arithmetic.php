<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Math;

Math\abs::<int>(-5); // 5 (preserves int|float type)
Math\ceil(4.2); // 5.0
Math\floor(4.8); // 4.0
Math\round(3.456, 2); // 3.46
Math\sqrt(16.0); // 4.0
Math\div(7, 2); // 3 (integer division, throws on division by zero)
