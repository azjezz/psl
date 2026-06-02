# Iter

The `Iter` component provides utility functions for inspecting and reducing iterables. Unlike Vec and Dict, which eagerly produce arrays, Iter functions work directly with any `iterable` -- arrays, generators, and iterators -- making them ideal for querying data without creating intermediate collections.

## When to Use Iter vs Vec/Dict

Use **Vec** or **Dict** when you need a transformed array as output (mapping, filtering, sorting). Use **Iter** when you need to answer a question about an iterable (does it contain X? what is the first element? how many items?) or reduce it to a single value.

```php
use Psl\Iter;
use Psl\Vec;

// Vec: "give me a new array with doubled values"
Vec\map([1, 2, 3], fn($n) => $n * 2); // [2, 4, 6]

// Iter: "what is the sum of these values?"
Iter\reduce([1, 2, 3], fn($sum, $n) => $sum + $n, 0); // 6
```

## Querying

```php
use Psl\Iter;

// First and last element (null if empty)
Iter\first([10, 20, 30]); // 10
Iter\last([10, 20, 30]); // 30

// Check for emptiness
Iter\is_empty([]); // true
Iter\is_empty([1]); // false

// Count elements
Iter\count([1, 2, 3]); // 3

// Contains: check if a value exists (strict equality)
Iter\contains([1, 2, 3], 2); // true
Iter\contains([1, 2, 3], '2'); // false (strict)

// Contains key: check if a key exists
Iter\contains_key(['a' => 1, 'b' => 2], 'a'); // true

// Random: get a random element
Iter\random([10, 20, 30, 40]); // e.g. 30
```

## Predicates

```php
use Psl\Iter;

// All: do all elements satisfy the predicate? (short-circuits on false)
Iter\all([2, 4, 6], fn(int $n) => ($n % 2) === 0); // true
Iter\all([2, 3, 6], fn(int $n) => ($n % 2) === 0); // false

// Any: does at least one element satisfy the predicate? (short-circuits on true)
Iter\any([1, 3, 5], fn(int $n) => $n > 4); // true
Iter\any([1, 3, 5], fn(int $n) => $n > 9); // false
```

## Searching

```php
use Psl\Iter;
use Psl\Str;

// Search: find the first value matching a predicate
Iter\search(['foo', 'bar', 'baz'], fn(string $v) => Str\starts_with($v, 'ba'));
// 'bar'

Iter\search([1, 2, 3], fn(int $v) => $v > 10);

// null (not found)
```

## Reducing

```php
use Psl\Iter;

// Reduce: fold an iterable into a single value
Iter\reduce([1, 2, 3, 4], fn(int $carry, int $v) => $carry + $v, 0);
// 10

// Reduce with keys: the callback also receives the key
$cart = ['apple' => 2, 'banana' => 3];
$prices = ['apple' => 1.50, 'banana' => 0.75];

Iter\reduce_with_keys($cart, fn(float $total, string $item, int $qty) => $total + ($prices[$item] * $qty), 0.0);

// 5.25
```

## Side Effects

```php
use Psl\Iter;

// Apply: execute a function on each element (returns void)
Iter\apply(['alice', 'bob'], fn(string $name) => print "Hello, {$name}!\n");

// Prints:
//   Hello, alice!
//   Hello, bob!
```

## Rewindable Iterators

Generators in PHP can only be iterated once. The `Iter\Iterator` class wraps a generator so it can be rewound and iterated multiple times without re-executing the generator.

```php
use Psl\Iter;

$gen = (function () {
    yield 'a';
    yield 'b';
    yield 'c';
})();

$iterator = Iter\rewindable($gen);

Iter\count($iterator); // 3
$iterator->rewind();
Iter\first($iterator); // 'a' -- still accessible after counting
```

See [src/Psl/Iter/](https://github.com/php-standard-library/php-standard-library/tree/6.0.0/packages/iter/src/Psl/Iter/) for the full API.
