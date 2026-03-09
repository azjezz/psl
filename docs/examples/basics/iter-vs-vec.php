<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;
use Psl\Vec;

// Vec: "give me a new array with doubled values"
Vec\map([1, 2, 3], fn($n) => $n * 2); // [2, 4, 6]

// Iter: "what is the sum of these values?"
Iter\reduce([1, 2, 3], fn($sum, $n) => $sum + $n, 0); // 6
