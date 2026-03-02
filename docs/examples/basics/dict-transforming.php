<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Dict;
use Psl\Str;

// Map: transform values, keep keys intact
Dict\map(['a' => 1, 'b' => 2, 'c' => 3], fn(int $v) => $v * 10);
// ['a' => 10, 'b' => 20, 'c' => 30]

// Map keys: transform keys, keep values intact
Dict\map_keys(['a' => 1, 'b' => 2], fn(string $k) => Str\uppercase($k));
// ['A' => 1, 'B' => 2]

// Map with key: callback receives both key and value
Dict\map_with_key(['width' => 100, 'height' => 50], fn(string $k, int $v) => $k . '=' . $v);

// ['width' => 'width=100', 'height' => 'height=50']
