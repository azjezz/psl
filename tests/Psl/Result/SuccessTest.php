<?php

declare(strict_types=1);

namespace Psl\Tests\Result;

use PHPUnit\Framework\TestCase;
use Psl\Result\Success;
use Psl\Exception\InvariantViolationException;

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
}
