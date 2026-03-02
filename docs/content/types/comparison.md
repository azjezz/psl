# Comparison

The `Comparison` component provides interfaces and functions for comparing values in a type-safe, consistent way. Instead of relying on PHP's loose comparison rules or scattering `<=>` operators throughout your code, you implement a `Comparable` interface and get a full suite of comparison operations for free.

## Design

- **`Comparable<T>`** -- Interface for types that can be ordered relative to each other
- **`Equable<T>`** -- Interface for types that can be checked for equality
- **`Order`** -- Enum with three cases: `Less`, `Equal`, `Greater`
- **Comparison functions** -- `compare()`, `equal()`, `less()`, `greater()`, and more

## Usage

### The Order Enum

`Order` replaces magic integers (`-1`, `0`, `1`) with readable, type-safe cases:

@example('types/comparison-order.php')

### Comparing Values

The `compare()` function works with any values. For types implementing `Comparable`, it delegates to their `compare()` method. For everything else, it falls back to PHP's `<=>` operator:

@example('types/comparison-values.php')

### Implementing Comparable

Make your classes sortable and comparable by implementing the `Comparable` interface:

@example('types/comparison-comparable.php')

### Sorting with Comparable

The `sort()` function returns an `int` suitable for PHP's `usort()` or PSL's `Vec\sort`:

@example('types/comparison-sorting.php')

### Available Comparison Functions

All functions work with both `Comparable` types and plain scalars:

| Function | Description |
|---|---|
| `compare($a, $b)` | Returns `Order` enum |
| `equal($a, $b)` | True if values are equal |
| `not_equal($a, $b)` | True if values differ |
| `less($a, $b)` | True if `$a < $b` |
| `greater($a, $b)` | True if `$a > $b` |
| `less_or_equal($a, $b)` | True if `$a <= $b` |
| `greater_or_equal($a, $b)` | True if `$a >= $b` |
| `sort($a, $b)` | Returns `int` for use as a sort callback |

See `src/Psl/Comparison/` for the full API.
