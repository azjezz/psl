<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;

$both = new EitherOrBoth\Both('l', 'r');

// Exclusive predicates: true for exactly one variant
$both->isLeft(); // false
$both->isRight(); // false
$both->isBoth(); // true

// Inclusive predicates: true whenever the side is present
$both->hasLeft(); // true -- Both has a left side
$both->hasRight(); // true -- Both has a right side

$leftOnly = new EitherOrBoth\Left('l');
$leftOnly->isLeft(); // true
$leftOnly->hasLeft(); // true
$leftOnly->hasRight(); // false
