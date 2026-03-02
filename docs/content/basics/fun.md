# Fun

The `Fun` component provides functional programming utilities for composing, decorating, and controlling function execution. These small combinators let you build data pipelines, inject side effects without disrupting flow, and defer expensive computations until they are actually needed.

## Usage

### Piping Values Through Functions

`pipe()` chains closures left-to-right. Each function receives the output of the previous one:

@example('basics/fun-pipe.php')

### Composing Two Functions

`after()` connects two functions where the output type of the first can differ from the input type of the second:

@example('basics/fun-after.php')

### Side Effects with tap()

`tap()` runs a callback for its side effect and returns the original value unchanged -- useful for logging or debugging inside a pipeline:

@example('basics/fun-tap.php')

### Lazy Evaluation

`lazy()` wraps an initializer so the value is computed once on first access, then cached:

@example('basics/fun-lazy.php')

### Conditional Logic

`when()` returns one of two results based on a predicate:

@example('basics/fun-when.php')

### Utility Functions

`identity()` returns a closure that passes its argument through unchanged -- handy as a default transformer or no-op callback:

@example('basics/fun-identity.php')

`rethrow()` returns a closure that rethrows any exception it receives -- useful as an error callback:

@example('basics/fun-rethrow.php')

See `src/Psl/Fun/` for the full API.
