<?php

declare(strict_types=1);

namespace Psl\Tests\Asio;

use Psl\Asio;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;

class WrapTest extends TestCase
{
    public function testWrapException(): void
    {
        $exception = new \Exception('foo');
        $wrapper = Asio\wrap(static function() use ($exception): void {
            throw $exception;
        });
        $this->assertFalse($wrapper->isSucceeded());
        $this->assertTrue($wrapper->isFailed());
        $this->assertSame($exception, $wrapper->getException());

        $this->expectExceptionObject($exception);

        $wrapper->getResult();
    }

    public function testWrapResult(): void
    {
        $wrapper = Asio\wrap(static function(): string {
            return 'foo';
        });
        $this->assertTrue($wrapper->isSucceeded());
        $this->assertFalse($wrapper->isFailed());
        $this->assertSame('foo', $wrapper->getResult());

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No exception thrown');

        $wrapper->getException();
    }
}
