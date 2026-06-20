<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Vec;

// Range: generate a sequence of numbers
Vec\range::<int>(1, 5); // [1, 2, 3, 4, 5]
Vec\range::<float>(0.0, 1.0, 0.5); // [0.0, 0.5, 1.0]

// Reproduce: generate values using a factory
Vec\reproduce::<int>(3, fn(int $i) => $i * $i);
// [1, 4, 9]

// Unique: remove duplicate values
Vec\unique::<int>([1, 2, 2, 3, 3, 3]);
// [1, 2, 3]

// Reverse: flip the order
Vec\reverse::<int>([1, 2, 3]);
// [3, 2, 1]

// Enumerate: convert key-value pairs into a list of [key, value] tuples
Vec\enumerate::<string, int>(['a' => 1, 'b' => 2]);
// [['a', 1], ['b', 2]]

// Keys/Values: extract keys or values as a list
Vec\keys::<string, int>(['x' => 10, 'y' => 20]); // ['x', 'y']
Vec\values::<int>(['x' => 10, 'y' => 20]); // [10, 20]
