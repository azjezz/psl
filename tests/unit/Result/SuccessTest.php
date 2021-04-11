<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Result;

use Exception;
use PHPUnit\Framework\TestCase;
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

    public function testGetException(): void
    {
        $wrapper = new Success('hello');

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No exception thrown');

        $wrapper->getException();
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
        static::assertSame($actual->getException(), $exception);
    }
}
