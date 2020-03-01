<?php

declare(strict_types=1);

namespace Psl\Tests\Asio;

use PHPUnit\Framework\TestCase;
use Psl\Asio\WrappedResult;
use Psl\Exception\InvariantViolationException;

class WrappedResultTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new WrappedResult('hello');
        $this->assertTrue($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new WrappedResult('hello');
        $this->assertFalse($wrapper->isFailed());
    }

    public function testGetResult(): void
    {
        $wrapper = new WrappedResult('hello');
        $this->assertSame('hello', $wrapper->getResult());
    }

    public function testGetException(): void
    {
        $wrapper = new WrappedResult('hello');

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('No exception thrown');

        $wrapper->getException();
    }
}
