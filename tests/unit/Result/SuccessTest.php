<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Exception\InvariantViolationException;
use Psl\Fun;
use Psl\Result\Success;

final class SuccessTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new Success('hello');
        static::assertTrue($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new Success('hello');
        static::assertFalse($wrapper->isFailed());
    }

    public function testGetResult(): void
    {
        $wrapper = new Success('hello');
        static::assertSame('hello', $wrapper->getResult());
    }

    public function testUnwrap(): void
    {
        $result = new Success('foo');
        $value = $result->unwrapOr(null);

        static::assertSame('foo', $value);
    }

    public function testGetException(): void
    {
        $wrapper = new Success('hello');

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No exception thrown');

        $wrapper->getThrowable();
    }

    public function testProceed(): void
    {
        $wrapper = new Success('hello');
        $actual  = $wrapper->proceed(
            static fn (string $result): int => 200,
            static fn (Exception $exception): int => 404
        );

        static::assertSame(200, $actual);
    }

    public function testThenToSuccess(): void
    {
        $wrapper = new Success('hello');
        $actual    = $wrapper->then(
            Fun\identity(),
            Fun\rethrow()
        );

        static::assertNotSame($wrapper, $actual);
        static::assertTrue($actual->isSucceeded());
        static::assertSame('hello', $actual->getResult());
    }

    public function testThenToFailure(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Success('hello');
        $actual    = $wrapper->then(
            static function () use ($exception) {
                throw $exception;
            },
            Fun\rethrow()
        );

        static::assertFalse($actual->isSucceeded());
        static::assertSame($actual->getThrowable(), $exception);
    }

    public function testCatch(): void
    {
        $wrapper = new Success('hello');
        $actual    = $wrapper->catch(static function () {
            throw new Exception('Dont call us, we\'ll call you!');
        });

        static::assertNotSame($wrapper, $actual);
        static::assertTrue($actual->isSucceeded());
        static::assertSame('hello', $actual->getResult());
    }

    public function testMap(): void
    {
        $wrapper = new Success('hello');
        $actual    = $wrapper->map(Fun\identity());

        static::assertNotSame($wrapper, $actual);
        static::assertTrue($actual->isSucceeded());
        static::assertSame('hello', $actual->getResult());

        $wrapper = new Success('hello');
        $actual    = $wrapper->map(static fn() => throw new Exception('bye'));

        static::assertNotSame($wrapper, $actual);
        static::assertFalse($actual->isSucceeded());
        static::assertTrue($actual->isFailed());
        static::assertSame('bye', $actual->getThrowable()->getMessage());
    }

    public function testAlways(): void
    {
        $ref = new Psl\Ref('');
        $wrapper = new Success('hello');
        $actual    = $wrapper->always(static function () use ($ref) {
            $ref->value .= 'hey';
        });

        static::assertNotSame($wrapper, $actual);
        static::assertTrue($actual->isSucceeded());
        static::assertFalse($actual->isFailed());
        static::assertSame('hey', $ref->value);
        static::assertSame('hello', $actual->getResult());
    }
}
