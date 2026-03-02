# Collection

The `Collection` component provides generic, object-oriented collection types as an alternative to PHP arrays. Collections are strongly typed, support generics via doc-block annotations, and offer a rich API for filtering, mapping, and slicing.

Each collection type comes in two variants: an immutable (`readonly`) version and a mutable version.

| Type | Immutable | Mutable |
|------|-----------|---------|
| Ordered list | `Vector<T>` | `MutableVector<T>` |
| Key-value map | `Map<Tk, Tv>` | `MutableMap<Tk, Tv>` |
| Unique values | `Set<T>` | `MutableSet<T>` |

## Vector

A `Vector` is an ordered, integer-indexed sequence of values -- similar to a PHP `list<T>`.

@example('collections/collection-vector.php')

## Map

A `Map` is an ordered collection of key-value pairs. Keys must be `int` or `string`.

@example('collections/collection-map.php')

## Set

A `Set` stores unique values with no duplicate entries. Values must be `int` or `string` (array-key types).

@example('collections/collection-set.php')

## Common Operations

All collection types share a consistent API for reading, transforming, and slicing:

@example('collections/collection-common-operations.php')

All collections implement `IteratorAggregate` for use in `foreach`, and `JsonSerializable` for JSON encoding. Maps always serialize as JSON objects (`{}`), vectors and sets as JSON arrays (`[]`).

Mutable collections also implement `ArrayAccess`:

@example('collections/collection-array-access.php')

See `src/Psl/Collection/` for the full API.
