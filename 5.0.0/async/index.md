# Async

The `Async` component brings concurrency into PHP using [cooperative multitasking](https://en.wikipedia.org/wiki/Cooperative_multitasking).

> **Note**
>
> The Async component is built on top of [RevoltPHP](https://github.com/revoltphp/event-loop), which makes it compatible with [Amphp](https://github.com/amphp),
> and other libraries that use the same event loop.

## Quick Start

```php
use Psl\Async;
use Psl\IO;
use Psl\Shell;

Async\main(static function (): int {
    $watcher = Async\Scheduler::onSignal(SIGINT, function (): never {
        IO\write_error_line('SIGINT received, stopping...');
        exit(0);
    });

    Async\Scheduler::unreference($watcher);

    IO\write_error_line('Press Ctrl+C to stop');

    Async\concurrently([
        static fn(): string => Shell\execute('sleep', ['3']),
        static fn(): string => Shell\execute('echo', ['Hello World!']),
        static fn(): string => Shell\execute('echo', ['Hello World!']),
    ]);

    IO\write_error_line('Done!');

    return 0;
});
```

## Core Concepts

### Entry Point

`Async\main()` is the entry point for async applications. It executes a closure in the main fiber, then keeps the event loop running until all pending callbacks complete. The closure must return an integer exit code.

```php
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

Async\main(static function (): int {
    Async\Scheduler::delay(Duration::seconds(1), static function (): void {
        IO\write_line('hello');
    });

    return 0;
});

// Output:
// hello
```

### Running Async Operations

`Async\run()` creates a new fiber and returns an `Awaitable` that resolves to its result:

```php
use Psl\Async;
use Psl\DateTime\Duration;

$awaitable = Async\run(static function (): string {
    Async\sleep(Duration::seconds(1));
    return 'Hello world!';
});

$result = $awaitable->await(); // 'Hello world!'
```

### Awaitables

An `Awaitable` is a promise-like object representing a value that may not yet be available. It can be awaited, mapped, chained, and composed.

```php
use Psl\Async;
use Psl\Str;

$awaitable = Async\run(static fn() => 'hello');

// Chain transformations
$awaitable = $awaitable->map(static fn($result) => Str\format('%s world', $result));

$result = $awaitable->await(); // 'hello world'
```

Error handling works naturally with `catch()`:

```php
use Psl\Async;

$awaitable = Async\run(static function (): string {
    throw new Exception('Something went wrong!');
});

$awaitable = $awaitable->catch(static fn($error) => $error->getMessage());

$result = $awaitable->await(); // 'Something went wrong!'
```

You can also iterate awaitables in completion order, regardless of the order they were started:

```php
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$handles = [
    Async\run(static function () {
        Async\sleep(Duration::seconds(1));
        return 'a';
    }),
    Async\run(static fn() => 'b'),
    Async\run(static function () {
        Async\sleep(Duration::milliseconds(300));
        return 'c';
    }),
    Async\run(static function () {
        Async\sleep(Duration::milliseconds(100));
        return 'd';
    }),
];

foreach (Async\Awaitable::iterate($handles) as $k => $awaitable) {
    $result = $awaitable->await();
    IO\write_line($k . ': ' . $result);
}

// Output:
// 1: b
// 3: d
// 2: c
// 0: a
```

## Combinators

### concurrently -- Run Tasks in Parallel

Runs all closures concurrently and returns their results in the original order:

```php
use Psl\Async;
use Psl\DateTime\Duration;

$results = Async\concurrently([
    static function (): string {
        Async\sleep(Duration::milliseconds(100));
        return 'users created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'organizations created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(75));
        return 'roles created';
    },
]);
```

> **Warning**
>
> `concurrently(...)` is about kicking-off I/O functions concurrently, not about concurrent execution of code.
> If your functions do not use any timers or perform any non-blocking I/O, they will actually be executed in series.

Use `Psl\Result\reflect(...)` to continue execution even when individual tasks fail:

```php
use Psl\Async;
use Psl\Result;
use Psl\Shell;

[$version, $foo] = Async\concurrently([
    Result\reflect(static fn() => Shell\execute('php', ['-v'])),
    Result\reflect(static fn() => Shell\execute('php', ['-r', 'foo();'])),
]);

// $version->isSucceeded() === true
// $foo->isFailed() === true
```

### series -- Run Tasks Sequentially

Runs closures one after another. If any throws, execution stops:

```php
use Psl\Async;
use Psl\DateTime\Duration;

$results = Async\series([
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'users created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'organizations created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'roles created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'user organization roles created';
    },
]);
```

### all -- Await Multiple Awaitables

Waits for all `Awaitable`s to complete. If multiple fail, throws `CompositeException`:

```php
use Psl\Async;
use Psl\Shell;

Async\all([
    Async\run(static fn() => Shell\execute('echo', ['tests passed'])),
    Async\run(static fn() => Shell\execute('echo', ['analysis passed'])),
    Async\run(static fn() => Shell\execute('echo', ['formatting ok'])),
]);
```

### any / first -- Race Awaitables

`any()` returns the first successful result. `first()` returns the first completed result regardless of success or failure:

```php
use Psl\Async;

// Returns 'hello' -- the first successful result
$result = Async\any([
    Async\Awaitable::error(new Exception('failed')),
    Async\Awaitable::complete('hello'),
]);
```

### sleep / later

`Async\sleep()` provides a non-blocking sleep. Multiple concurrent sleeps run in parallel:

```php
use Psl\Async;
use Psl\DateTime\Duration;

$time = time();

Async\concurrently([
    static fn() => Async\sleep(Duration::seconds(2)),
    static fn() => Async\sleep(Duration::seconds(2)),
    static fn() => Async\sleep(Duration::seconds(2)),
]);

// Total time: ~2 seconds, not 6
```

`Async\later()` reschedules the current fiber, allowing other pending callbacks to execute.

## Concurrency Control

### Semaphore

Limits the number of concurrent operations. All operations use the same processing function:

```php
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$semaphore = new Async\Semaphore(2, static function (int $input): void {
    IO\write_error_line('> started : %d', $input);
    Async\sleep(Duration::seconds(1));
    IO\write_error_line('> finished: %d', $input);
});

Async\concurrently([
    fn() => $semaphore->waitFor(1),
    fn() => $semaphore->waitFor(2),
    fn() => $semaphore->waitFor(3),
]);

// Output:
// > started: 1
// > started: 2
// > finished: 1
// > started: 3
// > finished: 2
// > finished: 3

$semaphore->cancel(new Exception('shutting down'));
```

The semaphore provides methods to inspect state (`getPendingOperations()`, `getIngoingOperations()`, `hasPendingOperations()`) and to cancel pending work.

### KeyedSemaphore

Like `Semaphore`, but applies concurrency limits per key. This is useful when you want to limit concurrent access to individual resources:

```php
use Psl\Async;
use Psl\DateTime\Duration;

$semaphore = new Async\KeyedSemaphore(2, static function (string $key, int $input): void {
    Async\sleep(Duration::seconds(1));
});

Async\concurrently([
    fn() => $semaphore->waitFor('foo', 1), // starts immediately
    fn() => $semaphore->waitFor('foo', 2), // starts immediately (limit 2 for 'foo')
    fn() => $semaphore->waitFor('foo', 3), // waits for one 'foo' to finish
    fn() => $semaphore->waitFor('bar', 1), // starts immediately (separate key)
]);
```

### Sequence

A specialized semaphore with a concurrency limit of 1 -- operations run one at a time:

```php
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$sequence = new Async\Sequence(static function (int $input): void {
    IO\write_error_line('> started : %d', $input);
    Async\sleep(Duration::seconds(1));
    IO\write_error_line('> finished: %d', $input);
});

Async\concurrently([
    fn() => $sequence->waitFor(1),
    fn() => $sequence->waitFor(2),
    fn() => $sequence->waitFor(3),
]);

// Output:
// > started: 1
// > finished: 1
// > started: 2
// > finished: 2
// > started: 3
// > finished: 3
```

### KeyedSequence

Like `Sequence`, but applies the sequential constraint per key. Different keys can run concurrently while the same key is serialized:

```php
use Psl\Async;
use Psl\DateTime\Duration;

$sequence = new Async\KeyedSequence(static function (string $key, int $input): void {
    Async\sleep(Duration::seconds(1));
});

Async\concurrently([
    fn() => $sequence->waitFor('foo', 1), // starts immediately
    fn() => $sequence->waitFor('foo', 2), // waits for foo:1
    fn() => $sequence->waitFor('bar', 1), // starts immediately (different key)
]);
```

## Deferred

> **Warning**
>
> The `Deferred` API is an advanced API that many applications probably don't need.
> Use `run(...)` and other combinators when possible.

`Deferred` is the low-level primitive for resolving future values. It produces an `Awaitable` that is completed manually:

```php
use Psl\Async;
use Psl\DateTime\Duration;

/**
 * @return Async\Awaitable<'hello'>
 */
function get_message(): Async\Awaitable
{
    /** @var Async\Deferred<'hello'> $deferred */
    $deferred = new Async\Deferred();

    // Complete the deferred with 'hello' after 2 seconds.
    Async\Scheduler::delay(Duration::seconds(2), static fn() => $deferred->complete('hello'));

    return $deferred->getAwaitable();
}

get_message()->await(); // 'hello'
```

The `Deferred` and `Awaitable` are intentionally separated: always return `$deferred->getAwaitable()` to API consumers. If you're passing `Deferred` objects around, you're probably doing something wrong.

## Scheduler

The `Scheduler` is a wrapper around the Revolt event loop. It provides static methods for registering callbacks:

- `Scheduler::defer($callback)` -- execute on next tick
- `Scheduler::delay($duration, $callback)` -- execute after a delay
- `Scheduler::repeat($interval, $callback)` -- execute repeatedly
- `Scheduler::onSignal($signal, $callback)` -- execute on OS signal
- `Scheduler::onReadable($stream, $callback)` -- execute when stream is readable
- `Scheduler::onWritable($stream, $callback)` -- execute when stream is writable
- `Scheduler::queue($callback)` -- queue a microtask

All registration methods return a string identifier that can be used with `cancel()`, `enable()`, `disable()`, `reference()`, and `unreference()`.

See [revolt.run](https://revolt.run/) for more information on the underlying event loop.

## Error Handling

- **`CompositeException`** -- wraps multiple exceptions when several concurrent operations fail. Use `$e->getReasons()` to get all underlying exceptions.
- **`TimeoutException`** -- thrown when a task exceeds its timeout.
- **`UnhandledAwaitableException`** -- thrown by the scheduler when a failed `Awaitable` is never awaited or handled. Use `$awaitable->ignore()` to suppress this.

To implement a timeout, use `Async\Scheduler::delay()` to schedule cancellation:

```php
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$deferred = new Async\Deferred();

// Schedule a timeout after 1 second
$timeout = Async\Scheduler::delay(Duration::seconds(1), static function () use ($deferred): void {
    $deferred->error(new Async\Exception\TimeoutException('Task timed out'));
});

$awaitable = Async\run(static function () use ($deferred, $timeout): void {
    Async\sleep(Duration::seconds(4));
    Async\Scheduler::cancel($timeout);
    $deferred->complete(null);
});

try {
    $deferred->getAwaitable()->await();
} catch (Async\Exception\TimeoutException) {
    IO\write_line('Task timed out as expected');
}
```

See [src/Psl/Async/](https://github.com/php-standard-library/php-standard-library/tree/5.0.0/src/Psl/Async/) for the full API.
