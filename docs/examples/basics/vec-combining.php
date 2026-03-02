<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;
use Psl\Vec;

// Concat: join multiple iterables into one list
Vec\concat([1, 2], [3, 4], [5]);
// [1, 2, 3, 4, 5]

// Flatten: merge a list of lists into a single list
Vec\flatten([[1, 2], [3], [4, 5]]);
// [1, 2, 3, 4, 5]

// Flat map: map then flatten in one step
Vec\flat_map(['hello world', 'foo bar'], fn($s) => Str\split($s, ' '));
// ['hello', 'world', 'foo', 'bar']

// Zip: pair up elements from two lists
Vec\zip(['a', 'b', 'c'], [1, 2, 3]);
// [['a', 1], ['b', 2], ['c', 3]]

// Chunk: split into groups of a given size
Vec\chunk([1, 2, 3, 4, 5], 2);
// [[1, 2], [3, 4], [5]]

// Partition: split into two lists based on a predicate
Vec\partition([1, 2, 3, 4, 5], fn($n) => ($n % 2) === 0);

// [[2, 4], [1, 3, 5]]
