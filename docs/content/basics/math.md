# Math

The `Math` component provides mathematical functions with strict typing and predictable error handling. Instead of PHP's loose math functions that silently return `false` or emit warnings, PSL's `Math` functions throw typed exceptions on invalid inputs and preserve numeric types through generics.

## Constants

@example('basics/math-constants.php')

## Usage

### Basic Arithmetic

@example('basics/math-arithmetic.php')

### Clamping and Aggregation

@example('basics/math-clamping.php')

### Min, Max, and Comparisons

@example('basics/math-min-max.php')

### Trigonometry

All trigonometric functions work in radians:

@example('basics/math-trigonometry.php')

### Logarithms and Exponentiation

@example('basics/math-logarithms.php')

### Base Conversion

@example('basics/math-base-conversion.php')

See `src/Psl/Math/` for the full API.
