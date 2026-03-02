<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DataStructure;

$stack = new DataStructure\Stack();

$stack->push('first');
$stack->push('second');
$stack->push('third');

$stack->count(); // 3

$stack->peek(); // "third" (does not remove)
$stack->pull(); // "third" (removes and returns)
$stack->pop(); // "second" (removes and returns, throws if empty)
