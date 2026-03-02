<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Range;

// Add an upper bound to a FromRange
$from = Range\from(0);
$between = $from->withUpperBoundInclusive(10);
// Now a BetweenRange: 0..=10

// Remove the lower bound
$to = $between->withoutLowerBound();
// Now a ToRange: ..=10

// Add a lower bound to a ToRange
$between = $to->withLowerBound(5);

// Now a BetweenRange: 5..=10
