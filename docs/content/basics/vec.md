# Vec

The `Vec` component provides functions for creating and transforming sequential, 0-indexed arrays (`list<T>`). Every Vec function returns a re-indexed list, making them a type-safe, predictable replacement for PHP's `array_map`, `array_filter`, `array_values`, and similar functions.

Unlike PHP's built-in array functions, Vec functions guarantee consistent argument order (iterable first, callback second), never preserve keys, and always produce a `list<T>`.

## Transforming Values

@example('basics/vec-transforming.php')

## Sorting

@example('basics/vec-sorting.php')

## Combining and Splitting

@example('basics/vec-combining.php')

## Generating and Reshaping

@example('basics/vec-generating.php')

## Key Difference from Dict

Vec always re-indexes the result. If you filter an associative array with `Vec\filter`, the keys are discarded and the result is a sequential `list<T>`. If you need to preserve keys, use `Dict\filter` instead.

@example('basics/vec-vs-dict.php')

See `src/Psl/Vec/` for the full API.
