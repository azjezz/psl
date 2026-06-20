<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Vec;

// Map: transform every element
Vec\map::<int, int, int>([1, 2, 3], fn(int $n) => $n * 2);
// [2, 4, 6]

// Map with key: the callback receives both key and value
Vec\map_with_key::<int, string, string>(['a', 'b', 'c'], fn(int $i, string $v) => $i . ':' . $v);
// ['0:a', '1:b', '2:c']

// Filter: keep elements matching a predicate
Vec\filter::<int>([1, 2, 3, 4, 5], fn(int $n) => $n > 3);
// [4, 5]

// Filter nulls: remove null values from a list
Vec\filter_nulls::<int>([1, null, 3, null, 5]);
// [1, 3, 5]

// Filter nonnull by: keep values where the callback returns non-null (original values are preserved)
Vec\filter_nonnull_by::<int, string>(['hello', '', 'world'], fn(string $v) => $v !== '' ? $v : null);
// ['hello', 'world']

// Map nonnull: transform values and discard null results
Vec\map_nonnull::<int, int, int>([1, 2, 3], fn(int $v) => $v > 1 ? $v * 2 : null);

// [4, 6]
