<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Dict;
use Psl\Str;

// Filter by value (keys are preserved)
Dict\filter(['a' => 1, 'b' => 0, 'c' => 3], fn(int $v) => $v > 0);
// ['a' => 1, 'c' => 3]

// Filter by key
Dict\filter_keys(['admin' => true, 'guest' => false], fn(string $k) => $k !== 'guest');
// ['admin' => true]

// Filter with both key and value
Dict\filter_with_key(['foo' => 1, 'bar' => 2, 'baz' => 3], fn(string $k, int $v) => $v > 1 && Str\contains($k, 'a'));

// ['bar' => 2, 'baz' => 3]
