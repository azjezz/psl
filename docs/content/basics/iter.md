# Iter

The `Iter` component provides utility functions for inspecting and reducing iterables. Unlike Vec and Dict, which eagerly produce arrays, Iter functions work directly with any `iterable` -- arrays, generators, and iterators -- making them ideal for querying data without creating intermediate collections.

## When to Use Iter vs Vec/Dict

Use **Vec** or **Dict** when you need a transformed array as output (mapping, filtering, sorting). Use **Iter** when you need to answer a question about an iterable (does it contain X? what is the first element? how many items?) or reduce it to a single value.

@example('basics/iter-vs-vec.php')

## Querying

@example('basics/iter-querying.php')

## Predicates

@example('basics/iter-predicates.php')

## Searching

@example('basics/iter-searching.php')

## Reducing

@example('basics/iter-reducing.php')

## Side Effects

@example('basics/iter-side-effects.php')

## Joining Two Iterables

`Iter\merge_join_by` and `Iter\merge_join_by_key` perform a full outer join of two iterables, yielding an `EitherOrBoth` event for each element that is present on the left only, the right only, or both. The sorted variant is lazy and uses O(1) memory on first traversal; the keyed variant materializes the right-hand side into a lookup and streams the left. Both return a rewindable `Iter\Iterator` -- a second iteration replays the cached events without re-walking the inputs.

@example('basics/iter-joining.php')

See the [EitherOrBoth](#either-or-both) page for the shape of the events and patterns for consuming them.

## Rewindable Iterators

Generators in PHP can only be iterated once. The `Iter\Iterator` class wraps a generator so it can be rewound and iterated multiple times without re-executing the generator.

@example('basics/iter-rewindable.php')

See `src/Psl/Iter/` for the full API.
