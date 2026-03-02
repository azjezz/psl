<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;

// First and last element (null if empty)
Iter\first([10, 20, 30]); // 10
Iter\last([10, 20, 30]); // 30

// Check for emptiness
Iter\is_empty([]); // true
Iter\is_empty([1]); // false

// Count elements
Iter\count([1, 2, 3]); // 3

// Contains: check if a value exists (strict equality)
Iter\contains([1, 2, 3], 2); // true
Iter\contains([1, 2, 3], '2'); // false (strict)

// Contains key: check if a key exists
Iter\contains_key(['a' => 1, 'b' => 2], 'a'); // true

// Random: get a random element
Iter\random([10, 20, 30, 40]); // e.g. 30
