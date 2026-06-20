<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;

// First and last element (null if empty)
Iter\first::<int>([10, 20, 30]); // 10
Iter\last::<int>([10, 20, 30]); // 30

// Check for emptiness
Iter\is_empty::<int>([]); // true
Iter\is_empty::<int>([1]); // false

// Count elements
Iter\count::<int>([1, 2, 3]); // 3

// Contains: check if a value exists (strict equality)
Iter\contains::<int>([1, 2, 3], 2); // true
Iter\contains::<int>([1, 2, 3], '2'); // false (strict)

// Contains key: check if a key exists
Iter\contains_key::<string, int>(['a' => 1, 'b' => 2], 'a'); // true

// Random: get a random element
Iter\random::<int>([10, 20, 30, 40]); // e.g. 30
