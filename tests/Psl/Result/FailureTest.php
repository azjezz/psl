<?php

declare(strict_types=1);

namespace Psl\Tests\Result;

use Exception;
use PHPUnit\Framework\TestCase;
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

    public function testGetException(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $e         = $wrapper->getException();
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
        static::assertSame($actual->getException(), $exception);
    }
}
