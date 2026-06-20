<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Collection\MutableVector;

/**
 * @var MutableVector<int> $v
 */
$v = MutableVector::fromArray::<int>([10, 20, 30]);
$v[] = 40; // appends
$v[1] = 99; // sets index 1
unset($v[0]); // removes index 0
