<?php

declare(strict_types=1);

namespace Psl\Tests\Result;

use PHPUnit\Framework\TestCase;
use Psl\Result\Failure;

class FailureTest extends TestCase
{
    public function testIsSucceeded(): void
    {
        $wrapper = new Failure(new \Exception('foo'));
        self::assertFalse($wrapper->isSucceeded());
    }

    public function testIsFailed(): void
    {
        $wrapper = new Failure(new \Exception('foo'));
        self::assertTrue($wrapper->isFailed());
    }

    public function testGetResult(): void
    {
        $exception = new \Exception('bar');
        $wrapper   = new Failure($exception);

        $this->expectExceptionObject($exception);
        $wrapper->getResult();
    }

    public function testGetException(): void
    {
        $exception = new \Exception('bar');
        $wrapper   = new Failure($exception);
        $e         = $wrapper->getException();
        self::assertSame($exception, $e);
    }
}
