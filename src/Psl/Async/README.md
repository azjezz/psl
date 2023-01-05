# Async

The `Async` component brings concurrency into PHP using [cooperative multitasking](https://en.wikipedia.org/wiki/Cooperative_multitasking).

> **Note**
>
> The Async component is built on top of [RevoltPHP](https://github.com/revoltphp/event-loop), which makes it compatible with [Amphp](https://github.com/amphp),
> and other libraries that use the same event loop.

## Usage

```php
use Psl\IO;
use Psl\Async;
use Psl\Shell;

Async\main(static function(): int {
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

## API

### Functions

---

* [`main((Closure(): int)|(Closure(): Awaitable<int> $closure): never`](main.php)

    Execute `$closure` in `{main}` fiber, then exit with returned exit code.

    If `$closure` returns an `Awaitable`, it *MUST* resolve with an exit code.

    After executing `$closure`, the event loop will keep running until there's no more callbacks to be executed.

    ---

    ```php
    use Psl\Async;
    use Psl\IO;
    
    Async\main(static function(): int {
      Async\Scheduler::delay(1.0, static function(): void {
        IO\write_line('hello');
      });
    
      return 0;
    });
    
    // Output:
    // hello
    ```

---

* [`run<T>((Closure(): T) $closure): Async\Awaitable<T>`](run.php)

     Create a new fiber asynchronously using the given closure, and return an `Awaitable` that resolves to the result of the closure.

     If the closure throws an exception, the `Awaitable` will fail with that exception.

    ---

    ```php
    use Psl\Async;

    $awaitable = Async\run(static function (): string {
      Async\sleep(1);
      return 'Hello world!';
    });
    $result = $awaitable->await(); // 'Hello world!'
    ```

    ```php
    use Psl\Async;

    $awaitable = Async\run(static function (): string {
      throw new Exception('Something went wrong!');
    });

    try {
      $result = $awaitable->await(); // throws Exception
    } catch (Exception $e) {
      // ... handle exception
    }
    ```

---

* [`await<T>(Awaitable<T> $awaitable): T`](await.php)

    Await the given `Awaitable`, and return the result.

    If the `Awaitable` fails, the exception will be thrown.

    ---

    ```php
    use Psl\Async;

    $awaitable = Async\run(static function (): string {
      return 'Hello world!';
    });

    $result = Async\await($awaitable); // 'Hello world!'
    ```

---

* [`all<Tk of array-key, Tv>(iterable<Tk, Awaitable<Tv>> $awaitables): array<Tk, Tv>`](all.php)

    Awaits all `Awaitable`s to complete concurrently.

    If one `Awaitable` fails, the exception will be thrown immediately, and the result of the `Awaitable`s will be ignored.

    Once the `Awaitable`s have completed, an array containing the results will be returned preserving the original `Awaitable`s order.

    If multiple `Awaitable`s failed at once, `Exception\CompositeException` will be thrown.

    ---

    ```php
    use Psl\Async;
    use Psl\Shell;

    Async\all([
      Async\run(static fn() => Shell\execute('php', ['vendor/bin/phpunit', '-c', 'phpunit.xml.dist'])),
      Async\run(static fn() => Shell\execute('php', ['vendor/bin/psalm', '-c', 'psalm.xml'])),
      Async\run(static fn() => Shell\execute('php', ['vendor/bin/psalm', '-c', 'psalm.xml', '--taint-analysis'])),
      Async\run(static fn() => Shell\execute('php', ['vendor/bin/php-cs-fixer', 'fix', '--config=.php_cs.dist.php', '--dry-run'])),
      Async\run(static fn() => Shell\execute('php', ['vendor/bin/phpcs', '--standard=.phpcs.xml'])),
    ]);

    try {
      $result = Async\all([
        Async\Awaitable::error(new Exception('Something went wrong!')),
        Async\Awaitable::complete('hello'),
      ]);
    } catch (Exception $e) {
      // ... handle the exception
    }

    try {
      $result = Async\all([
        Async\Awaitable::error(new Exception('Something went wrong!')),
        Async\Awaitable::error(new Exception('Something else went wrong!')),
      ]);
    } catch (Async\Exception\CompositeException $e) {
      $reasons = $e->getReasons(); // [Exception, Exception]
      // ... handle the exceptions
    }
    ```

---

* [`any<T>(iterable<Awaitable<T>> $awaitables): T`](any.php)

    Return the first successfully completed `Awaitable` result.

    If you want the first `Awaitable` completed, successful or not, use `first(...)` instead.

    If multiple  `Awaitable`s completed successfully at once, the first one will be returned.

    If `$awaitables` is empty, `Psl\Exception\InvariantViolationException` will be thrown.

    If all `Awaitable`s failed, `Exception\CompositeException` will be thrown.

    ---

    ```php
    use Psl;
    use Psl\Async;

    $result = Async\any([
      Async\Awaitable::error(new Exception('Something went wrong!')),
      Async\Awaitable::complete('hello'),
    ]);

    Psl\invariant($result === 'hello', 'Should be hello!');

    try {
      $result = Async\any([]);
    } catch (Psl\Exception\InvariantViolationException $e) {
      // ... handle the exception
    }

    try {
      $result = Async\any([
        Async\Awaitable::error(new Exception('Something went wrong!')),
        Async\Awaitable::error(new Exception('Something else went wrong!')),
      ]);
    } catch (Async\Exception\CompositeException $e) {
      $reasons = $e->getReasons(); // [Exception, Exception]
      // ... handle the exceptions
    }
    ```

---

* [`first<T>(iterable<Awaitable<T>> $awaitables): T`](first.php)

    Return the first completed `Awaitable` result, or throw an exception if the first `Awaitable` failed.

    If you want the first `Awaitable` completed without an error, use `any(...)` instead.

    If `$awaitables` is empty, `Psl\Exception\InvariantViolationException` will be thrown.

    If all `Awaitable`s failed, `Exception\CompositeException` will be thrown.

    ---

    ```php
    use Psl;
    use Psl\Async;

    $result = Async\first([
      Async\Awaitable::complete('hello'),
      Async\Awaitable::error(new Exception('Something went wrong!')),
    ]);

    Psl\invariant($result === 'hello', 'Should be hello!');

    try {
      $result = Async\first([
        Async\Awaitable::error(new Exception('Something went wrong!')),
        Async\Awaitable::complete('hello'),
      ]);
    } catch (Exception $e) {
      // ... handle the exception
    }

    try {
      $result = Async\first([]);
    } catch (Psl\Exception\InvariantViolationException $e) {
      // ... handle the exception
    }

    try {
      $result = Async\first([
        Async\Awaitable::error(new Exception('Something went wrong!')),
        Async\Awaitable::error(new Exception('Something else went wrong!')),
      ]);
    } catch (Async\Exception\CompositeException $e) {
      $reasons = $e->getReasons(); // [Exception, Exception]
      // ... handle the exceptions
    }
    ```

---

* [`series<Tk of array-key, Tv>(iterable<Tk, (Closure(): Tv)> $tasks): array<Tk, Tv>`](series.php)

    Run the functions in the tasks' iterable in series, each one running once the previous function has completed.

    If any functions in the series throws, no more functions are run, and the exception is immediately thrown.

    ---

    ```php
    use Psl\Async;

    $results = Async\series([
      create_users(...),
      create_organizations(...),
      create_roles(...),
      create_user_organization_roles(...),
    ]);
    ```

---

* [`concurrently<Tk of array-key, Tv>(iterable<Tk, (Closure(): Tv)> $tasks): array<Tk, Tv>`](concurrently.php)

    Run the functions in the tasks' iterable in parallel, without waiting until the previous function has completed.

    If any functions in the tasks' iterable throws, no more functions are run, and the exception is immediately thrown.

    Once the tasks have completed, the results are returned as an array, preserving the original keys, in the order in which the tasks were passed.

    If `$tasks` is empty, an empty array will be returned.

    If multiple tasks throw at once, `Exception\CompositeException` will be thrown.

    ---

    ```php
    use Psl\Async;

    $results = Async\concurrently([
      create_users(...),
      create_organizations(...),
      create_roles(...),
      create_user_organization_roles(...),
    ]);
    ```

    ---

    > **Warning**
    >
    > `concurrently(...)` is about kicking-off I/O functions concurrently, not about concurrently execution of code.
    > If your functions do not use any timers or perform any non-blocking I/O, they will actually be executed in series.

    ---

    ```php
    use Psl\Async;

    use function file_get_contents;

    // the following runs in series, as `file_get_contents` is blocking.
    Async\concurrently([
      static fn() => file_get_contents('/etc/hosts'),
      static fn() => file_get_contents('/etc/resolv.conf'),
    ]);
    ```

    ---

    > **Note**
    >
    > use `Psl\Result\reflect(...)` to continue the execution of other functions when a function fails.

    ---

    ```php
    use Psl;
    use Psl\Async;
    use Psl\Result;
    use Psl\Shell;

    [$version, $foo] = Async\concurrently([
      Result\reflect(static fn() => Shell\execute('php', ['-v'])),
      Result\reflect(static fn() => Shell\execute('php', ['-r', 'foo();'])),
    ]);

    Psl\invariant($version->isSucceeded(), '`$ php -v` should have succeeded.');
    Psl\invariant($foo->isFailed(), '`$ php -r "foo();"` should have failed.');
    ```

---

* [`sleep(float $seconds): void`](sleep.php)

    Non-blocking sleep for the specified number of seconds.

    ---

    ```php
    use Psl;
    use Psl\Async;

    use function time;

    $time = time();

    Async\concurrently([
      static fn() => Async\sleep(2),
      static fn() => Async\sleep(2),
      static fn() => Async\sleep(2),
    ]);

    $duration = time() - $time;

    Psl\invariant(2 <= $duration < 3, 'Should sleep for 2 seconds.');
    ```

---

* [`later(): void`](later.php)

    Reschedule the work of an async function until some other time in the future.

    The common use case for this is if your async function actually has to wait for some blocking call,
    you can tell other callbacks in the async scheduler that they can work while this one waits for
    the blocking call to finish (e.g., maybe in a polling situation or something).

    ---

    ```php
    use Psl;
    use Psl\Async;

    $deferred = new Async\Deferred();

    // defer the execution of the callback until the next tick.
    Async\Schedule::defer(static fn() => $deferred->complete('hello'));

    Psl\invariant(!$deferred->isCompleted(), 'Deferred should not be completed yet.');

    Async\later();

    Psl\invariant($deferred->isComplete(), 'Deferred should be complete.');
    ```

### Classes

---

* [`final class Awaitable<T> implements Promise\PromiseInterface<T>`](Awaitable.php)

    An `Awaitable` is a promise that can be awaited.

    It can be used to wait for the result of an async function, or to wait for the result of a blocking call.

    It can also be used to wait for the result of a `Deferred`.

    ---

    ```php
    use Psl\Async;

    $awaitable = Async\run(static fn(): string => 'hello');
    $result = $awaitable->await(); // 'hello'
    ```

    ---

    * `static Awaitable::complete<Tv>(Tv $result): Awaitable<Tv>`

        Create an `Awaitable` that completes with the given value.

        ---

        ```php
        use Psl\Async;

        $awaitable = Async\Awaitable::complete('hello');
        $result = $awaitable->await(); // 'hello'
        ```

    ---

    * `static Awaitable::error(Exception $exception): Awaitable<never>`

        Create an `Awaitable` that fails with the given `$exception`.

        ---

        ```php
        use Psl\Async;

        $awaitable = Async\Awaitable::error(new Exception('Something went wrong!'));

        try {
          $awaitable->await();
        } catch (Exception $e) {
          // handle the exception
        }
        ```

    ---

    * `static Awaitable::iterate<Tk, Tv>(iterable<Tk, Awaitable<Tv>> $awaitables): Generator<Tk, Awaitable<Tv>, _, _>`

        Iterate over the given `Awaitable`s in completion order.

        ---

        ```php
        use Psl\IO;
        use Psl\Async;

        $handles = [
          Async\run(static function() {
            Async\sleep(1);
            return 'a';
          }),
          Async\run(static function() {
            return 'b';
          }),
          Async\run(static function() {
            Async\sleep(0.3);
            return 'c';
          }),
          Async\run(static function() {
            Async\sleep(0.1);
            return 'd';
          }),
        ];

        foreach(Async\Awaitable::iterate($handles) as $k => $awaitable) {
          $result = $awaitable->await();
          IO\writeLine($k . ': ' . $result);
        }

        // Output:
        // 1: b
        // 3: d
        // 2: c
        // 0: a
        ```

    ---

    * `Awaitable::isComplete(): bool`

        Returns `true` if the `Awaitable` is complete.

        ---

        ```php
        use Psl\Async;

        $awaitable = Async\Awaitable::complete('hello');

        Psl\invariant($awaitable->isComplete(), 'Should be complete.');

        $awaitable = Async\run(static fn() => Async\sleep(2));

        Psl\invariant(!$awaitable->isComplete(), 'Should not be complete.');
        ```

    ---

    * `Awaitable::await(): T`

        Await the result of the awaitable.

        ---

        ```php
        use Psl\Async;

        $awaitable = Async\run(static function(): string {
          Async\sleep(2);
          return 'hello';
        });

        Psl\invariant($awaitable->await() === 'hello', 'Should be "hello".');
        ```

    ---

    * `Awaitable::then<Ts>((Closure(T): Ts) $success, (Closure(Exception): Ts) $failure): Awaitable<Ts>`

        Attaches callbacks that are invoked when this `Awaitable` is completed.

        The returned `Awaitable` is resolved with the return value of the callback,
        or rejected with an exception thrown from the callback.

        ---

        ```php
        use Psl;
        use Psl\Async;
        use Psl\Str;

        $awaitable = Async\run(static function(): string {
          return 'hello';
        });

        $awaitable = $awaitable->then(
          static fn($result) => Str\format('%s world', $result),
          static fn($error) =>  Psl\invariant_violation('Should not throw.'),
        );

        $result = $awaitable->await(); // 'hello world'
        ```

    ---

    * `Awaitable::map<Ts>((Closure(T): Ts) $success): Awaitable<Ts>`

        Attaches a callback that is invoked if this `Awaitable` is completed successfully.

        The returned `Awaitable` is resolved with the return value of the callback,
        or rejected with an exception thrown from the callback.

        ---

        ```php
        use Psl\Async;
        use Psl\Str;

        $awaitable = Async\run(static function(): string {
          return 'hello';
        });

        $awaitable = $awaitable
          ->map(static fn($result) => Str\format('%s world', $result));

        $result = $awaitable->await(); // 'hello world'
        ```

    ---

    * `Awaitable::catch<Ts>((Closure(Exception): Ts) $failure): Awaitable<T|Ts>`

        Attaches a callback that is invoked if this `Awaitable` is completed with an error.

        The returned `Awaitable` is resolved with the return value of the callback,
        or rejected with an exception thrown from the callback.

        ---

        ```php
        use Psl\Async;

        $awaitable = Async\run(static function(): string {
          throw new Exception('Something went wrong!');
        });

        $awaitable = $awaitable
          ->catch(static fn($error) => $error->getMessage());

        $result = $awaitable->await(); // 'Something went wrong!'
        ```

    ---

    * `Awaitable::always((Closure(): void) $always): Awaitable<T>`

        Attaches a callback that is invoked when this `Awaitable` is completed.

        ---

        ```php
        use Psl\IO;
        use Psl\Async;

        $awaitable = Async\run(static function(): string {
          return 'hello';
        });

        $awaitable = $awaitable->always(static function(): void {
          IO\write_line('done');
        });

        $result = $awaitable->await(); // 'hello'
        // Output:
        // done
        ```

    ---

    * `Awaitable::ignore(): this`

        Do not forward unhandled errors to the event loop handler.

        ---

        ```php
        use Psl\Async;

        Async\run(static function(): string {
          throw new Exception('Something went wrong!');
        })->ignore();

        // No exception thrown
        Async\Scheduler::run();
        ```

---

* [`final class Semaphore<Tin, Tout>`](Semaphore.php)

    Run an operation with a limit on number of ongoing asynchronous jobs.

    All operations must have the same input type (`Tin`) and output type (`Tout`), and be processed by the same function.

    `Tin` may be a closure invoked by the `$operation` for maximum flexibility,
    however this pattern is best avoided in favor of creating semaphores with a more narrow process.

    ---

    ```php
    use Psl\Async;
    use Psl\IO;

    $semaphore = new Async\Semaphore(2, static function(int $input): void {
      IO\write_error_line('> started : %d', $input);
      Async\sleep(1);
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
    ```

    ---

    * `Semaphore::waitFor(Tin $input): Tout`

        Run the operation using the given `$input`.

        If the concurrency limit has been reached, this method will wait until one of the ingoing operations has completed.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(2, static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $handles = [];
        $handles[] = Async\run(fn() => $semaphore->waitFor(1));
        $handles[] = Async\run(fn() => $semaphore->waitFor(2));
        $handles[] = Async\run(fn() => $semaphore->waitFor(3));

        $results = Async\all($handles); // [2, 3, 4]
        ```

    ---

    * `Semaphore::cancel(Exception $exception): void`

        Cancel all pending operations.

        Any pending operation will fail with the given exception.

        Future operations will continue execution as usual.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(1, static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $one = Async\run(fn() => $semaphore->waitFor(1));
        $two = Async\run(fn() => $semaphore->waitFor(2));

        $semaphore->cancel(new Exception('foo'));

        $one->await(); // 2
        $two->await(); // throws `Exception` with message `foo`
        ```

    ---

    * `Semaphore::getConcurrencyLimit(): positive-int`

        Get the concurrency limit of this semaphore.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(2, static fn(int $input): void => $input + 1);

        $semaphore->getConcurrencyLimit(); // 2
        ```

    ---

    * `Semaphore::getPendingOperations(): int<0, max>`

        Get the number of pending operations.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(2, static fn(int $input): void => $input + 1);

        $semaphore->getPendingOperations(); // 0

        $one = Async\run(fn() => $semaphore->waitFor(1));
        $two = Async\run(fn() => $semaphore->waitFor(2));
        $three = Async\run(fn() => $semaphore->waitFor(3));
        $four = Async\run(fn() => $semaphore->waitFor(4));

        $semaphore->getPendingOperations(); // 2

        $one->await();

        $semaphore->getPendingOperations(); // 1

        $two->await();

        $semaphore->getPendingOperations(); // 0
        ```

    ---

    * `Semaphore::hasPendingOperations(): bool`

        Check if there are any pending operations.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(2, static fn(int $input): void => $input + 1);

        $semaphore->hasPendingOperations(); // false

        $one = Async\run(fn() => $semaphore->waitFor(1));
        $two = Async\run(fn() => $semaphore->waitFor(2));
        $three = Async\run(fn() => $semaphore->waitFor(3));
        $four = Async\run(fn() => $semaphore->waitFor(4));

        $semaphore->hasPendingOperations(); // true

        $one->await();

        $semaphore->hasPendingOperations(); // true

        $two->await();

        $semaphore->hasPendingOperations(); // false
        ```

    ---

    * `Semaphore::getIngoingOperations(): int<0, max>`

        Get the number of ingoing operations.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(2, static function(int $input): void {
            Async\sleep(1);

            return $input + 1;
        });

        $semaphore->getIngoingOperations(); // 0

        $one = Async\run(fn() => $semaphore->waitFor(1));

        $semaphore->getIngoingOperations(); // 1

        $two = Async\run(fn() => $semaphore->waitFor(2));

        $semaphore->getIngoingOperations(); // 2

        $one->await();

        $semaphore->getIngoingOperations(); // 1

        $two->await();

        $semaphore->getIngoingOperations(); // 0
        ```

    ---

    * `Semaphore::hasIngoingOperations(): bool`

        Check if there are any ingoing operations.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(2, static function(int $input): void {
            Async\sleep(1);

            return $input + 1;
        });

        $semaphore->hasIngoingOperations(); // false

        $one = Async\run(fn() => $semaphore->waitFor(1));

        $semaphore->hasIngoingOperations(); // true

        $two = Async\run(fn() => $semaphore->waitFor(2));

        $semaphore->hasIngoingOperations(); // true

        $one->await();

        $semaphore->hasIngoingOperations(); // true

        $two->await();

        $semaphore->hasIngoingOperations(); // false
        ```

    ---

    * `Semaphore::waitForPending(): void`

        Wait until all pending operations have completed.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\Semaphore(1, static function(int $input): void {
            Async\sleep(1);

            return $input + 1;
        });

        $one = Async\run(fn() => $semaphore->waitFor(1));
        $two = Async\run(fn() => $semaphore->waitFor(2));

        $semaphore->hasPendingOperations(); // true

        $semaphore->waitForPending();

        $semaphore->hasPendingOperations(); // false
        ```

---

* [`final class KeyedSemaphore<Tk of array-key, Tin, Tout>`](KeyedSemaphore.php)

    Run an operation with a limit on number of ongoing asynchronous jobs for a specific key.

    All operations must have the same input type (`Tin`) and output type (`Tout`), and be processed by the same function.

    `Tin` may be a closure invoked by the `$operation` for maximum flexibility,
    however this pattern is best avoided in favor of creating semaphores with a more narrow process.

    ---

    ```php
    use Psl\Async;
    use Psl\IO;

    $semaphore = new Async\KeyedSemaphore(2, static function(string $key, int $input): void {
      IO\write_error_line('> started(%s): %d', $key, $input);
      Async\sleep(1);
      IO\write_error_line('> finished(%s): %d', $key, $input);
    });

    Async\concurrently([
      fn() => $semaphore->waitFor('foo', 1),
      fn() => $semaphore->waitFor('foo', 2),
      fn() => $semaphore->waitFor('foo', 3),
      fn() => $semaphore->waitFor('bar', 1),
      fn() => $semaphore->waitFor('bar', 2),
      fn() => $semaphore->waitFor('bar', 3),
    ]);

    // Output:
    // > started(foo): 1
    // > started(foo): 2
    // > started(bar): 1
    // > started(bar): 2
    // > finished(foo): 1
    // > finished(bar): 1
    // > started(foo): 3
    // > started(bar): 3
    // > finished(foo): 2
    // > finished(bar): 2
    // > finished(foo): 3
    // > finished(bar): 3
    ```

    ---

    * `KeyedSemaphore::waitFor(Tk $key, Tin $input): Tout`

        Run the operation using the given `$input`.

        If the concurrency limit has been reached for the given `$key`, this method will wait until one of the ingoing operations has completed.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(2, static function(string $key, int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $handles = [];
        $handles[] = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $handles[] = Async\run(fn() => $semaphore->waitFor('foo', 2));
        $handles[] = Async\run(fn() => $semaphore->waitFor('foo', 3));
        $handles[] = Async\run(fn() => $semaphore->waitFor('bar', 4));
        $handles[] = Async\run(fn() => $semaphore->waitFor('bar', 5));
        $handles[] = Async\run(fn() => $semaphore->waitFor('bar', 6));

        $results = Async\all($handles); // [1, 2, 4, 5, 3, 6]
        ```

    ---

    * `KeyedSemaphore::cancel(string $key, Exception $exception): void`

        Cancel pending operations for the given key.

        Any pending operation will fail with the given exception.

        Future operations will continue execution as usual.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static function(string $_key, int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));

        $semaphore->cancel('foo', new Exception('foo'));

        $one->await(); // 2
        $two->await(); // throws `Exception` with message `foo`
        ```

    ---

    * `KeyedSemaphore::cancelAll(Exception $exception): void`

        Cancel all pending operations.

        Any pending operation will fail with the given exception.

        Future operations will continue execution as usual.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static function(string $_key, int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('bar', 2));

        $semaphore->cancelAll(new Exception('foo'));

        $one->await(); // throws `Exception` with message `foo`
        $two->await(); // throws `Exception` with message `foo`
        ```

    ---

    * `KeyedSemaphore::getConcurrencyLimit(): positive-int`

        Returns the concurrency limit for the semaphore.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(10, static fn(string $_key, int $input): void => $input + 1);
        $semaphore->getConcurrencyLimit(); // 10
        ```

    ---

    * `KeyedSemaphore::getPendingOperations(Tk $key): int<0, max>`

         Returns the number of pending operations for the given key.

        ---

         ```php
         use Psl\Async;

         $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

         $semaphore->getPendingOperations('foo'); // 0

         $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
         $two = Async\run(fn() => $semaphore->waitFor('foo', 2));
         $three = Async\run(fn() => $semaphore->waitFor('foo', 3));

         $semaphore->getPendingOperations('foo'); // 2
         $semaphore->getPendingOperations('bar'); // 0

         $one->await(); // 2
         $semaphore->getPendingOperations('foo'); // 1

         $two->await(); // 3
         $semaphore->getPendingOperations('foo'); // 0

         $three->await(); // 4
         $semaphore->getPendingOperations('foo'); // 0
         ```

    ---

    * `KeyedSemaphore::getTotalPendingOperations(): int<0, max>`

        Returns the total number of pending operations.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));
        $three = Async\run(fn() => $semaphore->waitFor('bar', 3));
        $four = Async\run(fn() => $semaphore->waitFor('bar', 4));

        $semaphore->getTotalPendingOperations(); // 2
        ```

    ---

    * `KeyedSemaphore::hasPendingOperations(Tk $key): bool`

        Check if there's any operations pending execution for the given key.

        If this method returns `true`, it means the semaphore has reached it's limits, future calls to `waitFor` will wait.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $semaphore->hasPendingOperations('foo'); // false

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));

        $semaphore->hasPendingOperations('foo'); // true
        ```

    ---

    * `KeyedSemaphore::hasAnyPendingOperations(): bool`

        Check if there's any operations pending execution.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $semaphore->hasAnyPendingOperations(); // false

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));

        $semaphore->hasAnyPendingOperations(); // true
        ```

    ---

    * `KeyedSemaphore::getIngoingOperations(Tk $key): int<0, max>`

        Returns the number of operations that are currently being executed for the given key.

        The returned number will always be lower, or equal to the concurrency limit.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $semaphore->getIngoingOperations('foo'); // 0

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));
        $three = Async\run(fn() => $semaphore->waitFor('foo', 3));

        $semaphore->getIngoingOperations('foo'); // 1
        $semaphore->getIngoingOperations('bar'); // 0

        $one->await(); // 2
        $semaphore->getIngoingOperations('foo'); // 1

        $two->await(); // 3
        $semaphore->getIngoingOperations('foo'); // 1

        $three->await(); // 4
        $semaphore->getIngoingOperations('foo'); // 0
        ```

    ---

    * `KeyedSemaphore::getTotalIngoingOperations(): int<0, max>`

        Returns the total number of operations that are currently being executed.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $semaphore->getTotalIngoingOperations(); // 0

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));
        $three = Async\run(fn() => $semaphore->waitFor('bar', 3));
        $four = Async\run(fn() => $semaphore->waitFor('bar', 4));

        $semaphore->getTotalIngoingOperations(); // 2
        ```

    ---

    * `KeyedSemaphore::hasIngoingOperations(Tk $key): bool`

        Check if there's any operations currently being executed for the given key.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $semaphore->hasIngoingOperations('foo'); // false

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));

        $semaphore->hasIngoingOperations('foo'); // true
        ```

    ---

    * `KeyedSemaphore::hasAnyIngoingOperations(): bool`

        Returns `true` if there's any operations currently being executed, `false` otherwise.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $semaphore->hasAnyIngoingOperations(); // false

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));

        $semaphore->hasAnyIngoingOperations(); // true
        ```

    ---

    * `KeyedSemaphore::waitForPending(Tk $key): void`

        Wait for all pending operations for the given key to complete.

        ---

        ```php
        use Psl\Async;

        $semaphore = new Async\KeyedSemaphore(1, static fn(string $_key, int $input): void => $input + 1);

        $one = Async\run(fn() => $semaphore->waitFor('foo', 1));
        $two = Async\run(fn() => $semaphore->waitFor('foo', 2));

        $semaphore->waitForPending('foo'); // waits for $one and $two to complete.
        ```

---

* [`final class Sequence<Tin, Tout>`](Sequence.php)

    Run an operation with a limit on number of ongoing asynchronous jobs of 1.

    Just like `Semaphore`, all operations must have the same input type `Tin` and output type `Tout`,
    and be processed by the same function.

    ---

    ```php
    use Psl\Async;
    use Psl\IO;

    $sequence = new Async\Sequence(static function(int $input): void {
      IO\write_error_line('> started : %d', $input);
      Async\sleep(1);
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

    ---

    * `Sequence::waitFor(Tin $input): Tout`

        Run the operation using the given `$input`, after all previous operations have completed.

        ---

        ```php
        use Psl\Async;

        $s = new Async\Sequence(static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $results = Async\concurrently([fn() => $s->waitFor(1), fn() => $s->waitFor(2), fn() => $s->waitFor(3)]); // [2, 3, 4]
        ```

    ---

    * `Sequence::cancel(Exception $exception): void`

        Cancel all pending operations.

        Any pending operation will fail with the given exception.

        Future operations will continue execution as usual.

        ---

        ```php
        use Psl\Async;

        $s = new Async\Sequence(static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $one = Async\run(fn() => $s->waitFor(1));
        $two = Async\run(fn() => $s->waitFor(2));

        $s->cancel(new Exception('foo'));

        $one->await(); // 2
        $two->await(); // throws `Exception` with message `foo`
        ```

    ---

    * `Sequence::getPendingOperations(): int<0, max>`

        Returns the number of operations that are currently pending.

        ---

        ```php
        use Psl\Async;

        $s = new Async\Sequence(static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $s->getPendingOperations(); // 0

        $one = Async\run(fn() => $s->waitFor(1));
        $two = Async\run(fn() => $s->waitFor(2));

        $s->getPendingOperations(); // 1
        ```

    ---

    * `Sequence::hasPendingOperations(): bool`

        Returns `true` if there's any operations currently pending, `false` otherwise.

        If this method returns `true`, it means future calls to `waitFor` will wait.

        ---

        ```php
        use Psl\Async;

        $s = new Async\Sequence(static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $s->hasPendingOperations(); // false

        $one = Async\run(fn() => $s->waitFor(1));

        $s->hasPendingOperations(); // true
        ```

    ---

    * `Sequence::hasIngoingOperations(): bool`

        Check if the sequence has any ingoing operations.

        If this method returns `true`, it means future calls to `waitFor` will wait.
        If this method returns `false`, it means future calls to `waitFor` will execute immediately.

        ---

        ```php
        use Psl\Async;

        $s = new Async\Sequence(static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $s->hasIngoingOperations(); // false

        $one = Async\run(fn() => $s->waitFor(1));

        $s->hasIngoingOperations(); // true

        $one->await(); // 2

        $s->hasIngoingOperations(); // false
        ```

    ---

    * `Sequence::waitForPending(): void`

        Wait for all pending operations to complete.

        ```php
        use Psl\Async;

        $s = new Async\Sequence(static function(int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $one = Async\run(fn() => $s->waitFor(1));
        $two = Async\run(fn() => $s->waitFor(2));

        $s->waitForPending(); // waits for $one and $two to complete.
        ```

---

* [`final class KeyedSequence<Tk of array-key, Tin, Tout>`](KeyedSequence.php)

    Run an operation with a limit on number of ongoing asynchronous jobs of 1.

    Just like `KeyedSemaphore`, all operations must have the same input type `Tin` and output type `Tout`,
    and be processed by the same function.

    ---

    ```php
    use Psl\Async;
    use Psl\IO;

    $sequence = new Async\KeyedSequence(1, static function(string $key, int $input): void {
      IO\write_error_line('> started(%s): %d', $key, $input);
      Async\sleep(1);
      IO\write_error_line('> finished(%s): %d', $key, $input);
    });

    Async\concurrently([
      fn() => $sequence->waitFor('foo', 1),
      fn() => $sequence->waitFor('foo', 2),
      fn() => $sequence->waitFor('foo', 3),
      fn() => $sequence->waitFor('bar', 1),
    ]);

    // Output:
    // > started(foo): 1
    // > started(bar): 1
    // > finished(foo): 1
    // > finished(bar): 1
    // > started(foo): 2
    // > finished(foo): 2
    // > started(foo): 3
    // > finished(foo): 3
    ```

    ---

    * `KeyedSequence::waitFor(Tk $key, Tin $input): Tout`

        Run the operation using the given `$input`, after all previous operations have completed.

        If the given `$key` is already in use, the operation will wait until the previous operation with the same `$key` has completed.

        ---

        ```php
        use Psl\Async;

        $s = new Async\KeyedSequence(1, static function(string $_key, int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $results = Async\concurrently([fn() => $s->waitFor('foo', 1), fn() => $s->waitFor('foo', 2), fn() => $s->waitFor('foo', 3)]); // [2, 3, 4]
        ```

    ---

    * `KeyedSequence::cancel(Tk $key, Exception $exception): void`

        Cancel all pending operations.

        Any pending operation will fail with the given exception.

        Future operations will continue execution as usual.

        ---

        ```php
        use Psl\Async;

        $s = new Async\KeyedSequence(1, static function(string $_key, int $input): void {
            Async\sleep(1);
            return $input + 1;
        });

        $one = Async\run(fn() => $s->waitFor('foo', 1));
        $two = Async\run(fn() => $s->waitFor('foo', 2));

        $s->cancel(new Exception('foo'));

        $one->await(); // 2

        try {
            $two->await();
        } catch (Exception $e) {
            echo $e->getMessage(); // foo
        }
        ```

    ---

    * `KeyedSequence::cancelAll(Exception $exception): void`

        Cancel all pending operations.

        Pending operation will fail with the given exception.

        Future operations will continue execution as usual.

    ---

    * `KeyedSequence::getPendingOperations(Tk $key): int<0, max>`

        Get the number of operations pending execution for the given key.

    ---

    * `KeyedSequence::getTotalPendingOperations(): int<0, max>`

        Get the total number of operations pending execution.

    ---

    * `KeyedSequence::hasPendingOperations(Tk $key): bool`

        Check if there's any operations pending execution for the given key.

        If this method returns `true`, it means the sequence is busy, future calls to `waitFor` will wait.

    ---

    * `KeyedSequence::hasAnyPendingOperations(): bool`

        Check if there's any operations pending execution.

    ---

    * `KeyedSequence::hasIngoingOperations(Tk $key): bool`

        Check if the sequence has any ingoing operations for the given key.

        If this method returns `true`, it means future calls to `waitFor` will wait.
        If this method returns `false`, it means future calls to `waitFor` will execute immediately.

    ---

    * `KeyedSequence::hasAnyIngoingOperations(): bool`

        Check if the sequence has any ingoing operations.

    ---

    * `KeyedSequence::getTotalIngoingOperations(): int<0, max>`

        Get the number of total ingoing operations.

    ---

    * `KeyedSequence::waitForPending(Tk $key): void`

        Wait for all pending operations for the given key to complete.

---

* [`final class Deferred<T>`](Deferred.php)

    > **Warning**
    >
    > The `Deferred` API described below is an advanced API that many applications probably don’t need.
    > Use `run(...)`, and other `Async` combinators when possible.

    `Deferred` is the abstraction responsible for resolving future values once they become available.

    A library that completes values asynchronously creates an `Deferred` and uses it to return an `Awaitable` to API consumers.

    Once the async library determines that the value is ready it completes the `Awaitable` held by the API consumer using methods on the linked `Deferred`.

    ---

    ```php
    use Psl;
    use Psl\Async;
    use Psl\IO;

    $deferred = new Async\Deferred();

    $deferred->getAwaitable()->then(
      static fn($result) => Psl\invariant($result === 'hello', 'Should be "hello".'),
      static fn($error) => Psl\invariant(false, 'Should not have failed.'),
    );

    $deferred->complete('hello');
    ```

    ---

    * `Deferred::getAwaitable()`

        Returns the `Awaitable` that will be completed when the value is available.

        `Deferred` and `Awaitable` are separated, so the consumer of the `Awaitable` can’t complete it.
        You should always return `$deferred->getAwaitable()` to API consumers.
        If you’re passing `Deferred` objects around, you’re probably doing something wrong.

        ---

        ```php
        use Psl\Async;

        /**
         * @return Async\Awaitable<'hello'>
         */
        function get_message(): Async\Awaitable
        {
          $deferred = new Async\Deferred();

          // Complete the deferred with 'hello' after 2 second.
          Async\Scheduler::delay(2, static fn() => $deferred->complete('hello'));

          return $deferred->getAwaitable();
        }

        get_message()->await(); // 'hello'
        ```

    ---

    * `Deferred::complete(T $value)`

        Completes the `Awaitable` with the given value.

        ---

        ```php
        use Psl\Async;

        $deferred = new Async\Deferred();
        $deferred->complete('hello');

        $result = $deferred->getAwaitable()->await(); // 'hello'
        ```

    ---

    * `Deferred::error(Exception $exception): void`

        Makes the `Awaitable` fail with the given `$exception`.

        ---

        ```php
        use Psl\Async;

        $deferred = new Async\Deferred();
        $deferred->error(new Exception('Something went wrong!'));

        try {
          $deferred->getAwaitable()->await();
        } catch (Exception $e) {
          // handle the exception...
        }
        ```

    ---

    * `Deferred::isComplete(): bool`

        Returns `true` if the `Awaitable` has been completed.

        ---

        ```php
        use Psl\Async;

        $deferred = new Async\Deferred();

        Psl\invariant(false === $deferred->isComplete(), 'Should be pending.');

        $deferred->complete('hello');

        Psl\invariant(true === $deferred->isComplete(), 'Should be complete.');
        ```

---

* [`final class Scheduler`](Scheduler.php)

    Psl wrapper around Revolt event-loop.

    See [revolt.run](https://revolt.run/) for more information.

    ---

    * `static Scheduler::createSuspension(): Revolt\EventLoop\Suspension`

        Create an object used to suspend and resume execution, either within a fiber or from {main}.

        ---

        ```php
        use Psl\Async;

        $suspension = Async\Scheduler::createSuspension();
        // schedule the resume of the suspension 2 seconds from now.
        Async\Scheduler::delay(2.0, static fn() => $suspension->resume());
        // suspend the suspension until it is resumed.
        $suspension->suspend();
        ```

    ---

    * `static Scheduler::onSignal(int $signal_number, (Closure(string, int): void) $callback): non-empty-string`

        Register a callback to be called when the given signal is received.

        Returns a unique identifier that can be used to cancel, enable or disable the callback.

        see [revolt documentation](https://revolt.run/signals) for more information.

    ---

    * `static Scheduler::onReadable(object|resource $stream, (Closure(string, object|resource): void) $callback): non-empty-string`

        Execute a callback when a stream resource becomes readable or is closed for reading.

        Returns a unique identifier that can be used to cancel, enable or disable the callback.

        see [revolt documentation](https://revolt.run/streams) for more information.

    ---

    * `static Scheduler::onWritable(object|resource $stream, (Closure(string, object|resource): void) $callback): non-empty-string`

        Execute a callback when a stream resource becomes writable or is closed for writing.

        Returns a unique identifier that can be used to cancel, enable or disable the callback.

        see [revolt documentation](https://revolt.run/streams) for more information.

    ---

    * `static Scheduler::defer((Closure(): void) $callback): non-empty-string`

        Defer the execution of a callback.

        Returns a unique identifier that can be used to cancel, enable or disable the callback.

        see [revolt documentation](https://revolt.run/timers) for more information.

    ---

    * `static Scheduler::delay(float $seconds, (Closure(): void) $callback): non-empty-string`

        Delay the execution of a callback.

        Returns a unique identifier that can be used to cancel, enable or disable the callback.

        see [revolt documentation](https://revolt.run/timers) for more information.

    ---

    * `static Scheduler::repeat(float $interval, (Closure(): void) $callback): non-empty-string`

        Repeatedly execute a callback.

        Returns a unique identifier that can be used to cancel, enable or disable the callback.

        see [revolt documentation](https://revolt.run/timers) for more information.

    ---

    * `static Scheduler::cancel(string $id): void`

        Cancel a callback.

        see [revolt documentation](https://revolt.run/fundamentals) for more information.

    ---

    * `static Scheduler::enable(string $id): void`

        Enable a callback.

        see [revolt documentation](https://revolt.run/fundamentals) for more information.

    ---

    * `static Scheduler::disable(string $id): void`

        Disable a callback.

        see [revolt documentation](https://revolt.run/fundamentals) for more information.

    ---

    * `static Scheduler::reference(string $id): void`

        Reference a callback.

        see [revolt documentation](https://revolt.run/fundamentals) for more information.

    ---

    * `static Scheduler::unreference(string $id): void`

        Remove a reference to a callback.

        see [revolt documentation](https://revolt.run/fundamentals) for more information.

    ---

    * `static Scheduler::queue((Closure(): void) $callback): void`

        Queue a microtask.

    ---

    * `static Scheduler::run(): void`

        Run the event loop.

        This method will wait until there's no more callbacks to execute.

        If a signal callback is registered, this method will block until the signal is received.

        see [revolt documentation](https://revolt.run/fundamentals) for more information.

### Exceptions

---

* [`final class Exception\ComositeException`](Exception/CompositeException.php)

    A `Exception\CompositeException` that can be used to wrap multiple `Exception`s.

    ---

    * `CompositeException::__construct(non-empty-array<array-key, Exception> $reasons)`

        Constructs a new `Exception\CompositeException` with the given `$reasons`.

        ---

        ```php
        use Psl\Async;

        $exception = new Async\Exception\CompositeException([
          new Exception('Something went wrong!'),
          new Exception('Something else went wrong!'),
        ]);
        ```

    ---

    * `CompositeException::getReasons(): non-empty-array<array-key, Exception>`

        Returns the `$reasons` that were wrapped.

        ---

        ```php
        use Psl\Async;

        $exception = new Async\Exception\CompositeException([
          new Exception('Something went wrong!'),
          new Exception('Something else went wrong!'),
        ]);

        $exceptions = $exception->getReasons();
        ```

---

* [`final class Exception\TimeoutException`](Exception/TimeoutException.php)

    A `Exception\TimeoutException` is thrown when a task is not completed within the given `$timeout`.

    ---

    ```php
    use Psl\Async;
    use Psl\IO;

    $awaitable = Async\run(static function(): void {
      Async\sleep(4);
    }, timeout: 1.0);

    try {
      $awaitable->await();
    } catch (Async\Exception\TimeoutException $exception) {
      IO\write_error_line('Task timed out!');
    }
    ```

---

* [`final class Exception\UnhandledAwaitableException`](Exception/UnhandledAwaitableException.php)

    A `Exception\UnhandledAwaitableException` is thrown from the scheduler when a failed `Awaitable` is not handled.

    ---

    ```php
    use Psl\Async;

    Async\run(static function(): void {
      throw new Exception('Something went wrong!');
    });

    try {
      Async\Scheduler::run();
    } catch (Async\Exception\UnhandledAwaitableException $exception) {
      IO\write_error_line('Unhandled awaitable!');
      IO\write_error_line('Previous exception: %s', $exception->getPrevious()->getMessage());
    }

    // Output:
    // Unhandled awaitable!
    // Previous exception: Something went wrong!
    ```
