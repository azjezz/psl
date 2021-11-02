<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\Awaitable;
use Psl\Async\Exception\UnhandledAwaitableException;
use Psl\Async\Internal\State;
use Psl\Dict;
use Psl\Exception\InvariantViolationException;
use Psl\Str;
use Throwable;

final class AwaitableTest extends TestCase
{
    public function testCompleteAwait(): void
    {
        $state = new State();
        $awaitable = new Awaitable($state);

        static::assertFalse($awaitable->isComplete());
        $state->complete('hello');
        static::assertTrue($awaitable->isComplete());

        $result = $awaitable->await();

        static::assertSame('hello', $result);
    }

    public function testErroredAwait(): void
    {
        $state = new State();
        $awaitable = new Awaitable($state);

        static::assertFalse($awaitable->isComplete());
        $state->error(new InvariantViolationException('foo'));
        static::assertTrue($awaitable->isComplete());

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('foo');

        $awaitable->await();
    }

    public function testDiscardedAwaitableError(): void
    {
        $state = new State();
        $awaitable = new Awaitable($state);

        static::assertFalse($awaitable->isComplete());
        $state->error(new InvariantViolationException('foo'));
        static::assertTrue($awaitable->isComplete());

        $this->expectException(UnhandledAwaitableException::class);
        $this->expectExceptionMessage('Unhandled awaitable error "Psl\Exception\InvariantViolationException", make sure to call `Awaitable::await()` before the awaitable is destroyed, or call `Awaitable::ignore()` to ignore exceptions.');

        unset($awaitable, $state);

        Async\later();
    }

    public function testDiscardedIgnoredAwaitableError(): void
    {
        $state = new State();
        $awaitable = new Awaitable($state);

        static::assertFalse($awaitable->isComplete());
        $state->error(new InvariantViolationException('foo'));
        static::assertTrue($awaitable->isComplete());

        $awaitable->ignore();

        unset($awaitable, $state);

        Async\later();
    }

    public function testIterate(): void
    {
        $iterator = Awaitable::iterate([
            'foo' => Awaitable::complete('foo'),
            'bar' => Awaitable::error(new InvariantViolationException('bar')),
            'baz' => Async\run(static function () {
                Async\usleep(100);

                throw new InvariantViolationException('baz');
            }),
            'qux' => Async\run(static function () {
                Async\usleep(200);

                return 'qux';
            }),
        ]);

        static::assertSame('foo', $iterator->key());
        static::assertSame('foo', $iterator->current()->await());

        $iterator->next();

        static::assertSame('bar', $iterator->key());

        try {
            $iterator->current()->await();
            static::fail();
        } catch (InvariantViolationException) {
            $this->addToAssertionCount(1);
        }

        $iterator->next();

        static::assertSame('baz', $iterator->key());

        try {
            $iterator->current()->await();
            static::fail();
        } catch (InvariantViolationException) {
            $this->addToAssertionCount(1);
        }

        $iterator->next();

        static::assertSame('qux', $iterator->key());
        static::assertSame('qux', $iterator->current()->await());
    }

    public function testIterateGenerator(): void
    {
        $generator1 = Async\run(static function (): iterable {
            yield 'foo' => 'foo';

            Async\usleep(300);

            yield 'bar' => 'bar';
        });

        $generator2 = Async\run(static function (): iterable {
            yield 'baz' => 'baz';

            Async\usleep(100);

            yield 'qux' => 'qux';
        });

        $generator3 = Async\run(static function () use ($generator1, $generator2): iterable {
            yield 'gen1' => $generator1;

            Async\usleep(200);

            yield 'gen2' => $generator2;
        })->await();

        $values = [];
        // Awaitable::iterate() to throw the first error based on completion order instead of argument order
        foreach (Awaitable::iterate($generator3) as $index => $awaitable) {
            $values[$index] = $awaitable->await();
        }

        static::assertArrayHasKey('gen1', $values);
        static::assertArrayHasKey('gen2', $values);

        $values['gen1'] = Dict\from_iterable($values['gen1']);
        $values['gen2'] = Dict\from_iterable($values['gen2']);

        static::assertSame(['foo' => 'foo', 'bar' => 'bar'], $values['gen1']);
        static::assertSame(['baz' => 'baz', 'qux' => 'qux'], $values['gen2']);
    }

    public function testThenOnSuccess(): void
    {
        $awaitable = Async\run(static function (): string {
            return 'hello';
        });

        $awaitable = $awaitable
            ->then(
                static fn(string $result) => Str\reverse($result),
                static fn(Throwable $exception) => exit(0),
            )
            ->then(
                static fn(string $result) => throw new InvariantViolationException($result),
                static fn(Throwable $exception) => exit(0),
            )
            ->then(
                static fn($result) => exit(0),
                static fn(Throwable $exception) => throw $exception,
            )
            ->then(
                static fn($result) => exit(0),
                static fn(Throwable $exception) => $exception->getMessage(),
            )
        ;

        static::assertSame('olleh', $awaitable->await());
    }
}
