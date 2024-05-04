<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Fun;
use Psl\Result\Failure;

final class FailureTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new Failure(new Exception('foo'));
        static::assertFalse($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new Failure(new Exception('foo'));
        static::assertTrue($wrapper->isFailed());
    }

    public function testGetResult(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);

        $this->expectExceptionObject($exception);
        $wrapper->getResult();
    }

    public function testUnwrapFailure(): void
    {
        $result = new Failure(new Exception());
        $value = $result->unwrapOr(null);

        static::assertNull($value);
    }

    public function testGetException(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $e         = $wrapper->getThrowable();
        static::assertSame($exception, $e);
    }

    public function testProceed(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->proceed(
            static fn (string $result): int => 200,
            static fn (Exception $exception): int => 404
        );

        static::assertSame(404, $actual);
    }

    public function testThenToSuccess(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->then(
            static function () {
                throw new Exception('Dont call us, we\'ll call you!');
            },
            static fn (Exception $exception): string => $exception->getMessage()
        );

        static::assertTrue($actual->isSucceeded());
        static::assertSame($actual->getResult(), 'bar');
    }

    public function testThenToFailure(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->then(
            static function () {
                throw new Exception('Dont call us, we\'ll call you!');
            },
            Fun\rethrow()
        );

        static::assertFalse($actual->isSucceeded());
        static::assertSame($actual->getThrowable(), $exception);
    }

    public function testCatch(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->catch(Fun\rethrow());

        static::assertFalse($actual->isSucceeded());
        static::assertSame($actual->getThrowable(), $exception);

        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->catch(static fn($exception) => $exception);

        static::assertTrue($actual->isSucceeded());
        static::assertSame($exception, $actual->getResult());
    }

    public function testMap(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->map(static function () {
            throw new Exception('Dont call us, we\'ll call you!');
        });

        static::assertTrue($actual->isFailed());
        static::assertSame($exception, $actual->getThrowable());
    }

    public function testAlways(): void
    {
        $ref = new Psl\Ref('');
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->always(static function () use ($ref) {
            $ref->value .= 'hello';
        });

        static::assertTrue($actual->isFailed());
        static::assertSame($exception, $actual->getThrowable());
        static::assertSame('hello', $ref->value);
    }
}
