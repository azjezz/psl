<?php

declare(strict_types=1);

namespace Psl\Tests\Result;

use PHPUnit\Framework\TestCase;
use Psl\Result;
use Psl\Exception\InvariantViolationException;

class WrapTest extends TestCase
{
    public function testWrapException(): void
    {
        $exception = new \Exception('foo');
        $wrapper   = Result\wrap(static function () use ($exception): void {
            throw $exception;
        });
        self::assertFalse($wrapper->isSucceeded());
        self::assertTrue($wrapper->isFailed());
        self::assertSame($exception, $wrapper->getException());

        $this->expectExceptionObject($exception);

        $wrapper->getResult();
    }

    public function testWrapResult(): void
    {
        $wrapper = Result\wrap(static function (): string {
            return 'foo';
        });
        self::assertTrue($wrapper->isSucceeded());
        self::assertFalse($wrapper->isFailed());
        self::assertSame('foo', $wrapper->getResult());

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No exception thrown');

        $wrapper->getException();
    }
}
