<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Math;

Math\to_base(255, 16); // 'ff'
Math\from_base('ff', 16); // 255
Math\base_convert('ff', 16, 2); // '11111111'
