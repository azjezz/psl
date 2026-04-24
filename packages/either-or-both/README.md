# PHP Standard Library - EitherOrBoth

Three-variant disjoint union type (`Left` / `Right` / `Both`) for values that may be present on either or both of two sides.

Inspired by Rust's [`itertools::EitherOrBoth`](https://docs.rs/itertools/latest/itertools/enum.EitherOrBoth.html) and Haskell's [`Data.These`](https://hackage.haskell.org/package/these).

Unlike `Psl\Either`, no side is privileged — there is no "happy path". The three variants are equal citizens.

---

## When to use

Any time two collections, streams, or sources describe the same domain with potential partial overlap. Concrete cases:

- **Three-way diff of two collections** — canonical producer is `Psl\Iter\merge_join_by` (sorted) / `merge_join_by_key` (keyed). Used for DB synchronization, cache invalidation, UI list reconciliation, ETL.
- **Zip with unequal lengths** — pairing two iterables without truncating the longer one. `Both(a, b)` for matched positions, `Left(a)` or `Right(b)` for overflow tails.
- **Layered configuration merging** — defaults vs overrides. `Left` = key only in defaults, `Right` = key only in overrides, `Both(default, override)` = key in both (inspect conflict before resolving).
- **Multi-source enrichment** — primary record (e.g. a DB row) paired with secondary data (e.g. a cache, external API, analytics store). `Both` = enriched, `Left` = unenriched, `Right` = orphan enrichment.
- **Dual-validation / parallel checks** — two validation paths that can independently fail. `Both(schemaError, businessError)` distinguishes fully-invalid from partially-invalid when the remediation differs.
- **Snapshot comparison** — expected vs actual state, old-format vs new-format records, current DB vs desired state from IaC.
- **API request/response pairing** — matching request IDs with response IDs: `Left` = outstanding, `Right` = unsolicited, `Both` = matched.
- **Migration dual-write verification** — during a schema/storage migration, detect records that lag on one side of the dual-write.

Wherever you would reach for "full outer join" in SQL, an `EitherOrBoth`-valued iterator is the idiomatic equivalent at the language level.

## Quick examples

### Three-way sync

```php
use Psl\EitherOrBoth;
use Psl\Iter;

foreach (Iter\merge_join_by_key($incoming, $current, fn($r) => $r->id) as $event) {
    $event->proceed(
        left:  fn($new)           => $repo->insert($new),
        right: fn($old)           => $repo->delete($old),
        both:  fn($new, $old)     => $repo->update($new, $old),
    );
}
```

### Layered config merge

```php
use Psl\EitherOrBoth;

function merge(array $defaults, array $overrides): array
{
    $result = [];
    foreach (alignByKey($defaults, $overrides) as $key => $e) {
        $result[$key] = $e->proceed(
            left:  fn($d)       => $d,
            right: fn($o)       => $o,
            both:  fn($d, $o)   => $o, // override wins
        );
    }
    return $result;
}
```

### Constructing directly

```php
use function Psl\EitherOrBoth\{left, right, both};

$onlyDefault   = left($default);
$onlyOverride  = right($override);
$conflict      = both($default, $override);

$conflict->isBoth();              // true
$conflict->hasLeft();             // true
$conflict->unwrapLeft()->unwrap(); // $default
```

### Transforming

```php
use Psl\EitherOrBoth\EitherOrBoth;

/** @var EitherOrBoth<User, User> $e */
$hydrated = $e->map($hydrate);                          // both sides, same transform
$renamed  = $e->mapAny($toUpper, $toLower);             // per-side, different transforms
$flipped  = $e->swap();                                 // Both(l, r) -> Both(r, l)
```

---

- [Documentation](https://php-standard-library.dev)
- [Contributing](https://github.com/php-standard-library/php-standard-library/blob/next/CONTRIBUTING.md)
- [Create Pull Request](https://github.com/php-standard-library/php-standard-library/pulls)
- [Report an Issue](https://github.com/php-standard-library/php-standard-library/issues)
