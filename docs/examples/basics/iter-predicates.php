<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;

// All: do all elements satisfy the predicate? (short-circuits on false)
Iter\all([2, 4, 6], fn(int $n) => ($n % 2) === 0); // true
Iter\all([2, 3, 6], fn(int $n) => ($n % 2) === 0); // false

// Any: does at least one element satisfy the predicate? (short-circuits on true)
Iter\any([1, 3, 5], fn(int $n) => $n > 4); // true
Iter\any([1, 3, 5], fn(int $n) => $n > 9); // false
