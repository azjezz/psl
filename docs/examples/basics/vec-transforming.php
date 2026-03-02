<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Vec;

// Map: transform every element
Vec\map([1, 2, 3], fn(int $n) => $n * 2);
// [2, 4, 6]

// Map with key: the callback receives both key and value
Vec\map_with_key(['a', 'b', 'c'], fn(int $i, string $v) => $i . ':' . $v);
// ['0:a', '1:b', '2:c']

// Filter: keep elements matching a predicate
Vec\filter([1, 2, 3, 4, 5], fn(int $n) => $n > 3);
// [4, 5]

// Filter nulls: remove null values from a list
Vec\filter_nulls([1, null, 3, null, 5]);

// [1, 3, 5]
