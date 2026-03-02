<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Math;

Math\clamp(15, 0, 10); // 10
Math\clamp(-5, 0, 10); // 0

Math\sum([1, 2, 3, 4]); // 10 (int)
Math\sum_floats([1.5, 2.5]); // 4.0 (float)
Math\mean([2, 4, 6]); // 4.0
Math\median([1, 3, 5, 7]); // 4.0
Math\mean([]); // null (empty list)
