<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Dict;
use Psl\Vec;

$data = ['a' => 1, 'b' => 2, 'c' => 3];

Vec\filter::<int>($data, fn($v) => $v > 1);
// [2, 3]  -- keys dropped, re-indexed as list

Dict\filter::<string, int>($data, fn($v) => $v > 1);

// ['b' => 2, 'c' => 3]  -- keys preserved
