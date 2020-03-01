<?php

declare(strict_types=1);

namespace Psl\Tests\Asio;

use Psl\Asio\WrappedException;
use PHPUnit\Framework\TestCase;

class WrappedExceptionTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new WrappedException(new \Exception('foo'));
        $this->assertFalse($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new WrappedException(new \Exception('foo'));
        $this->assertTrue($wrapper->isFailed());
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
        $this->assertSame($exception, $e);
    }
}
