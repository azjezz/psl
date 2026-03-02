# Promise

The `Promise` component provides a promise interface for deferred computations. A promise represents a value that will be available in the future -- either resolved with a success value or rejected with an exception. You attach callbacks to react to the outcome without blocking.

## Design

`PromiseInterface<T>` defines four methods for composing asynchronous operations:

- **`then()`** -- Handle both success and failure in one call
- **`map()`** -- Transform the resolved value
- **`catch()`** -- Recover from rejection
- **`always()`** -- Run cleanup logic regardless of outcome

Every method returns a new `PromiseInterface`, so you can chain them into pipelines.

## Usage

### Transforming a Resolved Value

`map()` applies a function to the resolved value. If the promise is rejected, the callback is skipped and the rejection propagates:

@example('async/promise-map.php')

### Recovering from Failure

`catch()` attaches a callback that is only invoked when the promise is rejected. The returned value becomes the new resolved value:

@example('async/promise-catch.php')

### Handling Both Cases with then()

`then()` is a shortcut for chaining `map()` and `catch()`:

@example('async/promise-then.php')

This is equivalent to:

@example('async/promise-then-equivalent.php')

### Cleanup with always()

`always()` runs a callback when the promise settles, regardless of whether it resolved or was rejected. The promise's value passes through unchanged (unless the callback throws):

@example('async/promise-always.php')

### Building Pipelines

Because every method returns a new promise, you can compose multi-step async workflows:

@example('async/promise-pipeline.php')

## When to Use Promise

- **Async operations** -- React to the outcome of concurrent tasks
- **Pipeline composition** -- Chain transformations that may fail at any step
- **Error boundaries** -- Catch and recover from failures at specific points in the chain
- **Resource cleanup** -- Use `always()` to guarantee cleanup runs

See `src/Psl/Promise/` for the full API.
