<?php

declare(strict_types=1);

namespace Psl\Tests\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Result\Failure;
use stdClass;

class FailureTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new Failure(new Exception('foo'));
        self::assertFalse($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new Failure(new Exception('foo'));
        self::assertTrue($wrapper->isFailed());
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
        self::assertSame($exception, $e);
    }

    public function testProceed(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->proceed(
            static fn (string $result): int => 200,
            static fn (Exception $exception): int => 404
        );

        self::assertSame(404, $actual);
    }

    public function testThenToSuccess(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->then(
            static function () {
                throw new \Exception('Dont call us, we\'ll call you!');
            },
            static fn (Exception $exception): string => $exception->getMessage()
        );

        self::assertTrue($actual->isSucceeded());
        self::assertSame($actual->getResult(), 'bar');
    }

    public function testThenToFailure(): void
    {
        $exception = new Exception('bar');
        $wrapper   = new Failure($exception);
        $actual    = $wrapper->then(
            static function () {
                throw new \Exception('Dont call us, we\'ll call you!');
            },
            Fun\rethrow()
        );

        self::assertFalse($actual->isSucceeded());
        self::assertSame($actual->getException(), $exception);
    }
}
