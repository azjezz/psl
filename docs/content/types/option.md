# Option

The `Option` component represents a value that may or may not be present. It replaces nullable types (`?T`) with explicit `Some`/`None` semantics, making the absence of a value intentional and impossible to ignore.

Instead of returning `null` and hoping callers remember to check, you return an `Option` that forces the caller to handle both cases.

## Design

`Option<T>` is a single `readonly` class with two states:

- **`Option::some($value)`** -- A value is present
- **`Option::none()`** -- No value is present

Convenience functions `some()`, `none()`, and `from_nullable()` are available in the `Psl\Option` namespace.

## Usage

### Creating Options

@example('types/option-creating.php')

### Checking and Unwrapping

@example('types/option-checking.php')

### Transforming Values

@example('types/option-transforming.php')

### Pattern Matching with proceed()

Handle both cases and return a unified result:

@example('types/option-proceed.php')

### Side Effects with apply()

Run a closure on the value without changing the Option:

@example('types/option-apply.php')

### Filtering

@example('types/option-filtering.php')

### Combining Options

@example('types/option-combining.php')

## When to Use Option

- **Repository lookups**: `find_by_id()` returns `Option<Entity>` instead of `?Entity`
- **Configuration**: `get_setting()` returns `Option<string>` -- absence is explicit
- **Chained lookups**: Use `andThen()` to chain fallible lookups without nested null checks
- **Safe data access**: Parse user input with `from_nullable()` and transform with `map()`

See `src/Psl/Option/` for the full API.
