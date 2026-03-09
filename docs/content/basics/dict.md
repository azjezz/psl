# Dict

The `Dict` component provides functions for creating and transforming associative arrays (`array<Tk, Tv>`). Unlike Vec, Dict functions preserve keys, making them the right choice when the relationship between keys and values matters.

Dict functions are type-safe replacements for PHP's `array_map`, `array_filter`, `array_combine`, `array_merge`, and similar functions, with consistent argument order and predictable behavior.

## Transforming Values and Keys

@example('basics/dict-transforming.php')

## Filtering

@example('basics/dict-filtering.php')

## Building Dicts

@example('basics/dict-building.php')

## Merging and Selecting

@example('basics/dict-merging.php')

## Sorting

@example('basics/dict-sorting.php')

## Grouping

@example('basics/dict-grouping.php')

See `src/Psl/Dict/` for the full API.
