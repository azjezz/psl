<?php

declare(strict_types=1);

namespace Psl\Tests\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Result\Success;
use Psl\Exception\InvariantViolationException;
use stdClass;

class SuccessTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new Success('hello');
        self::assertTrue($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new Success('hello');
        self::assertFalse($wrapper->isFailed());
    }

    public function testGetResult(): void
    {
        $wrapper = new Success('hello');
        self::assertSame('hello', $wrapper->getResult());
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

        self::assertSame(200, $actual);
    }

    public function testThenToSuccess(): void
    {
        $wrapper = new Success('hello');
        $actual    = $wrapper->then(
            Fun\identity(),
            Fun\rethrow()
        );

        self::assertNotSame($wrapper, $actual);
        self::assertTrue($actual->isSucceeded());
        self::assertSame('hello', $actual->getResult());
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

        self::assertFalse($actual->isSucceeded());
        self::assertSame($actual->getException(), $exception);
    }
}
