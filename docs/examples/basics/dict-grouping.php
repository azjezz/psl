<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Dict;

// Group by: partition values into buckets based on a key function
$words = ['apple', 'avocado', 'banana', 'blueberry', 'cherry'];
Dict\group_by::<string, string>($words, fn(string $w) => $w[0]);
// ['a' => ['apple', 'avocado'], 'b' => ['banana', 'blueberry'], 'c' => ['cherry']]

// Returning null from the key function excludes that element
Dict\group_by::<string, int>([1, 2, 3, 4, 5], static function (int $n): null|string {
    if ($n <= 2) {
        return null; // exclude 1 and 2
    }

    if (($n % 2) === 0) {
        return 'even';
    }

    return 'odd';
});

// ['odd' => [3, 5], 'even' => [4]]
