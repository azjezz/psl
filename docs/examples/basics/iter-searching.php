<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;
use Psl\Str;

// Search: find the first value matching a predicate
Iter\search::<string>(['foo', 'bar', 'baz'], fn(string $v) => Str\starts_with($v, 'ba'));
// 'bar'

Iter\search::<int>([1, 2, 3], fn(int $v) => $v > 10);

// null (not found)
