<?php

declare(strict_types=1);

namespace Psl\Result\Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Result;

final class WrapTest extends TestCase
{
    public function testWrapException(): void
    {
        $exception = new Exception('foo');
        $wrapper = Result\wrap::<void>(static function () use ($exception): void {
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
        $wrapper = Result\wrap::<string>(static fn(): string => 'foo');
        static::assertTrue($wrapper->isSucceeded());
        static::assertFalse($wrapper->isFailed());
        static::assertSame('foo', $wrapper->getResult());

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No exception thrown');

        $wrapper->getThrowable();
    }

    public function testWrapOtherResult(): void
    {
        $wrapper = Result\wrap::<Result\ResultInterface<string>>(static fn(): Result\ResultInterface<string> => new Result\Success::<string>('foo'));
        static::assertTrue($wrapper->isSucceeded());
        static::assertFalse($wrapper->isFailed());
        static::assertSame('foo', $wrapper->getResult()->getResult());
    }
}
