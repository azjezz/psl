<?php

declare(strict_types=1);

namespace Psl\Tests\Asio;

use PHPUnit\Framework\TestCase;
use Psl\Asio\WrappedException;

class WrappedExceptionTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new WrappedException(new \Exception('foo'));
        self::assertFalse($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new WrappedException(new \Exception('foo'));
        self::assertTrue($wrapper->isFailed());
    }

    public function testGetResult(): void
    {
        $exception = new \Exception('bar');
        $wrapper = new WrappedException($exception);

        $this->expectExceptionObject($exception);
        $wrapper->getResult();
    }

    public function testGetException(): void
    {
        $exception = new \Exception('bar');
        $wrapper = new WrappedException($exception);
        $e = $wrapper->getException();
        self::assertSame($exception, $e);
    }
}
