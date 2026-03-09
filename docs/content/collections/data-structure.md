# DataStructure

The `DataStructure` component provides three classic data structures: `Queue` (FIFO), `Stack` (LIFO), and `PriorityQueue`. They are generic, type-safe, and offer a consistent API with both safe and throwing access methods.

## Queue (FIFO)

A queue processes items in first-in, first-out order. Items are added to the back and removed from the front:

@example('collections/data-structure-queue.php')

Use `pull()` when an empty queue is a normal condition (it returns `null`). Use `dequeue()` when an empty queue is a bug (it throws `UnderflowException`).

## Stack (LIFO)

A stack processes items in last-in, first-out order. Items are added to and removed from the top:

@example('collections/data-structure-stack.php')

Use `pull()` for safe access (returns `null` if empty) and `pop()` when emptiness is unexpected (throws `UnderflowException`).

## PriorityQueue

A priority queue dequeues items with the highest priority first. Items with equal priority are dequeued in FIFO order:

@example('collections/data-structure-priority-queue.php')

If no priority is specified, items default to priority `0`.

## Common API

All three structures implement `Countable` and share a consistent pattern:

| Method | Queue | Stack | PriorityQueue |
|--------|-------|-------|---------------|
| Add item | `enqueue($item)` | `push($item)` | `enqueue($item, $priority)` |
| Peek at next | `peek()` | `peek()` | `peek()` |
| Remove (safe) | `pull()` | `pull()` | `pull()` |
| Remove (throws) | `dequeue()` | `pop()` | `dequeue()` |
| Count items | `count()` | `count()` | `count()` |

- `peek()` returns the next item without removing it, or `null` if empty
- `pull()` removes and returns the next item, or `null` if empty
- `dequeue()` / `pop()` removes and returns the next item, or throws `UnderflowException` if empty

See `src/Psl/DataStructure/` for the full API.
