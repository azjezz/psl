<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;
use Psl\Vec;

// Concat: join multiple iterables into one list
Vec\concat::<int>([1, 2], [3, 4], [5]);
// [1, 2, 3, 4, 5]

// Flatten: merge a list of lists into a single list
Vec\flatten::<int>([[1, 2], [3], [4, 5]]);
// [1, 2, 3, 4, 5]

// Flat map: map then flatten in one step
Vec\flat_map::<string, string>(['hello world', 'foo bar'], fn($s) => Str\split($s, ' '));
// ['hello', 'world', 'foo', 'bar']

// Zip: pair up elements from two lists
Vec\zip::<string, int>(['a', 'b', 'c'], [1, 2, 3]);
// [['a', 1], ['b', 2], ['c', 3]]

// Chunk: split into groups of a given size
Vec\chunk::<int>([1, 2, 3, 4, 5], 2);
// [[1, 2], [3, 4], [5]]

// Partition: split into two lists based on a predicate
Vec\partition::<int>([1, 2, 3, 4, 5], fn($n) => ($n % 2) === 0);

// [[2, 4], [1, 3, 5]]
