<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Math;

Math\log(Math\E); // 1.0 (natural logarithm)
Math\log(100.0, 10.0); // 2.0 (log base 10)
Math\exp(1.0); // ~E (e^1)

// Throws on invalid input instead of returning -INF or NAN
try {
    Math\log(-1.0);
} catch (Math\Exception\InvalidArgumentException $e) {
    echo $e->getMessage() . "\n";
}
