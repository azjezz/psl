# EitherOrBoth

`EitherOrBoth` is a three-variant disjoint union: `Left`, `Right`, or `Both`. It models a value that may be present on either of two sides, or on both simultaneously.

Unlike `Either` (which carries a loose "one vs the other" convention where `Right` is the happy path), `EitherOrBoth` treats all three variants as equal citizens. No side is privileged. The canonical use case is a three-way diff between two collections -- insert / delete / update events -- but the type is useful anywhere two partially-overlapping sources describe the same domain.

## Design

- **`Left<TLeft>`** -- only the left value is present
- **`Right<TRight>`** -- only the right value is present
- **`Both<TLeft, TRight>`** -- both sides are present simultaneously
- **`EitherOrBoth<TLeft, TRight>`** -- the common interface all three implement

Inspired by Rust's [`itertools::EitherOrBoth`](https://docs.rs/itertools/latest/itertools/enum.EitherOrBoth.html) and Haskell's `Data.These`.

## When to Use

Reach for `EitherOrBoth` any time two collections, streams, or sources describe the same domain with potential partial overlap:

- **Three-way diff / synchronization** -- the canonical producer. Pair with `Psl\Iter\merge_join_by` or `merge_join_by_key` to emit insert / delete / update events.
- **Layered configuration merge** -- defaults vs overrides. `Left` = key only in defaults, `Right` = key only in overrides, `Both(default, override)` = key in both (inspect the conflict before resolving).
- **Multi-source enrichment** -- a primary record paired with optional data from a cache or secondary store.
- **Dual-validation** -- two parallel validators that can both fail, where the handling differs.
- **Snapshot comparison** -- expected vs actual, current vs desired, old-format vs new-format.
- **Full outer join at the language level** -- any time a SQL `FULL OUTER JOIN` is the mental model.

## Usage

### Creating EitherOrBoth Values

Use the concrete classes or the `left()` / `right()` / `both()` free functions:

@example('types/either-or-both-creating.php')

### Predicates: `is*` vs `has*`

`EitherOrBoth` has two tiers of predicates. The exclusive `isLeft()` / `isRight()` / `isBoth()` each return true for exactly one variant. The inclusive `hasLeft()` / `hasRight()` return true whenever the corresponding side is present -- so `hasLeft()` is true for both `Left` and `Both`.

@example('types/either-or-both-predicates.php')

### Extracting Values

@example('types/either-or-both-extracting.php')

### Transforming Values

`mapLeft()` and `mapRight()` each transform one side, leaving the other untouched. On `Both`, only the addressed side changes. `mapAny()` applies two closures, one per side. `map()` applies the same closure to whichever side(s) are present -- useful when both sides have the same type:

@example('types/either-or-both-transforming.php')

### Pattern Matching with proceed()

`proceed()` takes three closures -- one per variant -- and dispatches to exactly one. Arguments are positional: `left`, `right`, then `both` (no happy-path convention applies here, since the three variants are equal citizens):

@example('types/either-or-both-proceed.php')

### Swapping Sides

`swap()` flips Left into Right and vice versa. On `Both(l, r)` it produces `Both(r, l)`:

@example('types/either-or-both-swap.php')

### Side Effects with apply()

`apply()` runs a single side-effect closure on whichever side(s) are present and returns the value unchanged. On `Both` it runs twice, once per side -- same invocation shape as `map()`. Useful for logging inside a pipeline:

@example('types/either-or-both-apply.php')

## When to Use EitherOrBoth vs Either

- **Either** -- two mutually exclusive outcomes; one is the primary/success path, the other is secondary/error.
- **EitherOrBoth** -- two sides that may independently be present, absent, or both present. No privileged side.

If "both present" is a meaningful state in your domain, use `EitherOrBoth`. If it cannot occur, use `Either`.

See `src/Psl/EitherOrBoth/` for the full API.
