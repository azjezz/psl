# Observer

The `Observer` component provides two interfaces for the classic Observer design pattern. A subject maintains a list of observers and notifies them when something changes. PSL's interfaces are generic, so observers receive the concrete subject type rather than a vague base class.

## Design

- **`SubjectInterface`** -- The object being watched. It can `subscribe()`, `unsubscribe()`, and `notify()` observers.
- **`ObserverInterface<T>`** -- The watcher. Its `update()` method receives the subject that triggered the notification, fully typed as `T`.

## Usage

The following example demonstrates a complete Subject/Observer implementation, including wiring them together:

@example('other/observer-pattern.php')

See `src/Psl/Observer/` for the full API.
