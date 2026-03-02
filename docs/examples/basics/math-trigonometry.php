<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Math;

Math\sin(Math\PI / 2); // 1.0
Math\cos(0.0); // 1.0
Math\tan(Math\PI / 4); // ~1.0
Math\asin(1.0); // ~PI/2
Math\atan2(1.0, 1.0); // ~PI/4 (two-argument arctangent)
