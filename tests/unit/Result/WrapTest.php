<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Result;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Result;

final class WrapTest extends TestCase
{
    public function testWrapException(): void
    {
        $exception = new Exception('foo');
        $wrapper = Result\wrap(static function () use ($exception): void {
            throw $exception;
        });
        static::assertFalse($wrapper->isSucceeded());
        static::assertTrue($wrapper->isFailed());
        static::assertSame($exception, $wrapper->getThrowable());

        $this->expectExceptionObject($exception);

        $wrapper->getResult();
    }

    public function testWrapResult(): void
    {
        $wrapper = Result\wrap(static function (): string {
            return 'foo';
        });
        static::assertTrue($wrapper->isSucceeded());
        static::assertFalse($wrapper->isFailed());
        static::assertSame('foo', $wrapper->getResult());

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No exception thrown');

        $wrapper->getThrowable();
    }

    public function testWrapOtherResult(): void
    {
        $wrapper = Result\wrap(static function (): Result\ResultInterface {
            return new Result\Success('foo');
        });
        static::assertTrue($wrapper->isSucceeded());
        static::assertFalse($wrapper->isFailed());
        static::assertSame('foo', $wrapper->getResult());
    }
}
