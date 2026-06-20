<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;
use Psl\Vec;

// Sort in ascending order
Vec\sort::<int>([3, 1, 2]);
// [1, 2, 3]

// Sort with a custom comparator
Vec\sort::<string>(['banana', 'apple', 'cherry'], fn($a, $b) => Str\length($a) <=> Str\length($b));
// ['apple', 'banana', 'cherry']

// Sort by a derived value
$users = [['name' => 'Charlie', 'age' => 30], ['name' => 'Alice', 'age' => 25]];
Vec\sort_by::<array, int>($users, fn($u) => $u['age']);

// [['name' => 'Alice', 'age' => 25], ['name' => 'Charlie', 'age' => 30]]
