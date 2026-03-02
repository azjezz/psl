# Psl

The root namespace provides foundational utilities used across all PSL components.

## Usage

@example('basics/psl-usage.php')

## Key Concepts

`invariant()` is PSL's assertion function. Unlike PHP's `assert()`, it is always evaluated (never stripped in production) and throws a typed exception (`InvariantViolationException`) rather than triggering an error. Use it to enforce preconditions that must never be violated.

`Ref` is a generic mutable reference wrapper. It exists because PHP closures capture variables by value by default. Wrapping a value in `Ref` lets you share mutable state across closures without using `&` references.

See `src/Psl/` for the full API.
