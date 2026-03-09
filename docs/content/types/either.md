# Either

The `Either` component represents a value that is one of two possible types: `Left` or `Right`. Unlike `Result` (which specifically models success/failure with exceptions), `Either` is a general-purpose disjoint union -- both sides carry arbitrary values.

This is useful when an operation has two equally valid outcomes that aren't naturally "success" or "error", such as returning cached data vs. fresh data, or routing a value down one of two processing paths.

By convention, `Left` represents the secondary/error case and `Right` represents the primary/success case, but this is only a convention -- both sides are first-class values.

## Design

- **`Left<TLeft>`** -- Holds a value of the left type
- **`Right<TRight>`** -- Holds a value of the right type
- **`Either<TLeft, TRight>`** -- The common interface both implement

## Usage

### Creating Either Values

@example('types/either-creating.php')

### Extracting Values

@example('types/either-extracting.php')

### Transforming Values

`mapRight()` transforms the right value, leaving a left untouched. `mapLeft()` does the opposite. `map()` transforms whichever side is present:

@example('types/either-transforming.php')

### Chaining with flatMap

When a transformation itself returns an `Either`, use `flatMapRight()` or `flatMapLeft()` to avoid nested `Either` values:

@example('types/either-flatmap.php')

### Pattern Matching with proceed()

Handle both cases and return a unified result. The right (happy-path) closure comes first:

@example('types/either-proceed.php')

### Swapping Sides

`swap()` flips a `Left` into a `Right` and vice versa:

@example('types/either-swap.php')

### Side Effects with apply()

Run a closure on the contained value without changing the `Either`:

@example('types/either-apply.php')

## When to Use Either vs Result

- **Result** -- The operation can succeed or throw. You want to capture exceptions as values.
- **Either** -- The operation has two valid outcomes. Both sides carry meaningful domain values, not exceptions.

See `src/Psl/Either/` for the full API.
