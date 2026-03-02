# Range

The `Range` component provides a way to create a range of integer values.

Ranges represent sets of integer values with optional lower and upper bounds. They support containment checks, iteration, and bound manipulation.

## Usage

### Creating Ranges

There are four types of ranges, created with constructor functions:

@example('collections/range-creating.php')

### Containment Checks

All range types support `contains()`:

@example('collections/range-containment.php')

### Iterating Over Ranges

Ranges with a lower bound (`FromRange` and `BetweenRange`) are iterable:

@example('collections/range-iterating.php')

### Manipulating Bounds

Ranges can be combined with new bounds to create different range types:

@example('collections/range-manipulating.php')

See `src/Psl/Range/` for the full API.
