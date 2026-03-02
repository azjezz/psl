# Result

The `Result` component represents a computation that either succeeded or failed. Instead of letting exceptions propagate uncontrolled, you capture the outcome as a value that can be inspected, transformed, and passed around.

This is useful when you want to defer error handling, aggregate results from multiple operations, or build transformation pipelines without scattering try/catch blocks throughout your code.

## Design

- **`Success<T>`** -- Wraps a successful return value of type `T`
- **`Failure<T>`** -- Wraps a `Throwable` from a failed operation
- **`ResultInterface<T>`** -- The common interface both implement

## Usage

### Wrapping Operations

The `wrap()` function executes a closure and captures the outcome:

@example('types/result-wrapping.php')

### Transforming Results

`map()` transforms a successful value, leaving failures untouched. `catch()` recovers from failures, leaving successes untouched:

@example('types/result-transforming.php')

`then()` handles both cases at once, returning a new `ResultInterface`:

@example('types/result-then.php')

### Pattern Matching with proceed()

The `proceed()` method unwraps the result by calling the appropriate closure and returning the value directly (not wrapped in a Result):

@example('types/result-proceed.php')

### Cleanup with always()

Run cleanup logic regardless of outcome:

@example('types/result-always.php')

### Convenience Functions

`try_catch()` wraps and recovers in a single step:

@example('types/result-try-catch.php')

`reflect()` converts a throwing closure into one that returns a Result:

@example('types/result-reflect.php')

### Collecting Statistics

When processing batches, `collect_stats()` summarizes outcomes:

@example('types/result-stats.php')

## When to Use Result

- **Batch processing**: Collect results from multiple operations without stopping at the first error
- **Pipeline transformations**: Chain `map()` calls to build data transformation pipelines
- **Deferred error handling**: Capture errors as values and handle them at the right level
- **API boundaries**: Return Result from service methods instead of throwing across layers

See `src/Psl/Result/` for the full API.
