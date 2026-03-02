<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Math;

// From a list (returns null if empty)
Math\max([3, 1, 4, 1, 5]); // 5
Math\min([3, 1, 4, 1, 5]); // 1

// From variadic arguments (requires at least two)
Math\maxva(3, 1, 4, 1, 5); // 5
Math\minva(3, 1, 4, 1, 5); // 1

// By a custom comparison function
$users = [['name' => 'Alice', 'age' => 30], ['name' => 'Bob', 'age' => 25]];
Math\max_by($users, fn($u) => $u['age']); // ['name' => 'Alice', 'age' => 30]
Math\min_by($users, fn($u) => $u['age']); // ['name' => 'Bob', 'age' => 25]
