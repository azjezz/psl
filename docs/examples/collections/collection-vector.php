<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Collection\MutableVector;
use Psl\Collection\Vector;
use Psl\Str;

// Immutable vector
$names = Vector::fromArray(['Alice', 'Bob', 'Charlie']);
$names->count(); // 3
$names->at(0); // 'Alice'
$names->first(); // 'Alice'
$names->last(); // 'Charlie'
$names->toArray(); // ['Alice', 'Bob', 'Charlie']

// Filtering and mapping return new immutable vectors
$short = $names->filter(fn(string $n): bool => Str\length($n) <= 3);
$upper = $names->map(Str\uppercase(...));

/**
 * Mutable vector -- supports in-place modification
 *
 * @var MutableVector<string> $tasks
 */
$tasks = MutableVector::fromArray(['eat', 'sleep']);
$tasks->add('code');
$tasks->remove(0); // removes 'eat', re-indexes
$tasks->toArray(); // ['sleep', 'code']
