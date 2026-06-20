<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;

// Reduce: fold an iterable into a single value
Iter\reduce::<int, int>([1, 2, 3, 4], fn(int $carry, int $v) => $carry + $v, 0);
// 10

// Reduce with keys: the callback also receives the key
$cart = ['apple' => 2, 'banana' => 3];
$prices = ['apple' => 1.50, 'banana' => 0.75];

Iter\reduce_with_keys::<string, int, float>($cart, fn(float $total, string $item, int $qty) => $total + ($prices[$item] * $qty), 0.0);

// 5.25
