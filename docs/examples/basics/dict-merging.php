<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Dict;

// Merge: combine multiple dicts (later values overwrite earlier ones)
Dict\merge(['a' => 1, 'b' => 2], ['b' => 99, 'c' => 3]);
// ['a' => 1, 'b' => 99, 'c' => 3]

// Select keys: pick only the specified keys
Dict\select_keys(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], ['a', 'c']);
// ['a' => 1, 'c' => 3]

// Flip: swap keys and values
Dict\flip(['a' => 1, 'b' => 2]);
// [1 => 'a', 2 => 'b']

// Unique: remove entries with duplicate values (keeps the first occurrence's key)
Dict\unique([1 => 'a', 2 => 'b', 3 => 'a']);

// [1 => 'a', 2 => 'b']
