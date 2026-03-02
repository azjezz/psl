<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Collection\Vector;

$v = Vector::fromArray([10, 20, 30]);
$v->at(1); // 20 (throws OutOfBoundsException if missing)
$v->get(1); // 20 (returns null if missing)
$v->linearSearch(20); // 1 (index of the value)

// Slicing operations return new collections
$v = Vector::fromArray([1, 2, 3, 4, 5]);
$v->take(3)->toArray(); // [1, 2, 3]
$v->drop(2)->toArray(); // [3, 4, 5]
$v->slice(1, 3)->toArray(); // [2, 3, 4]
$v->chunk(2); // Vector<Vector<int>> of [[1,2], [3,4], [5]]
