<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ref;

// Assert invariants: throws InvariantViolationException when the condition is false
$items = ['apple', 'banana', 'cherry'];

Psl\invariant($items !== [], 'Item list must not be empty.');

// Mutable reference wrapper: pass by reference without PHP's & syntax
$counter = new Ref(0);

$increment =
    /**
     * @param Ref<int> $counter
     */
    static function (Ref $counter, int $amount): void {
        $counter->value += $amount;
    };

$increment($counter, 5);
$increment($counter, 3);

echo $counter->value; // 8
