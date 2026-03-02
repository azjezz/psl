<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Dict;

// Sort by values (keys are preserved)
Dict\sort(['b' => 3, 'a' => 1, 'c' => 2]);
// ['a' => 1, 'c' => 2, 'b' => 3]

// Sort by keys
Dict\sort_by_key(['banana' => 2, 'apple' => 1, 'cherry' => 3]);

// ['apple' => 1, 'banana' => 2, 'cherry' => 3]
