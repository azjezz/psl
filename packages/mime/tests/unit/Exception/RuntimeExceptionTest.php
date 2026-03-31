<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Exception\ExceptionInterface;
use Psl\MIME\Exception\InvalidArgumentException;
use Psl\MIME\Headers;

final class RuntimeExceptionTest extends TestCase
{
    public function testCreateViaInvalidArgumentException(): void
    {
        $exception = InvalidArgumentException::create('test error message');

        static::assertInstanceOf(InvalidArgumentException::class, $exception);
        static::assertSame('test error message', $exception->getMessage());
    }

    public function testCreateTriggeredByHeadersFolding(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $headers = Headers::fromPairs([['Content-Type', 'text/plain']]);
        $headers->toFoldedString(softLimit: 0);
    }

    public function testCreateTriggeredByHeadersFoldingHardLimitZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $headers = Headers::fromPairs([['Content-Type', 'text/plain']]);
        $headers->toFoldedString(softLimit: 1, hardLimit: 0);
    }

    public function testCreateTriggeredByHeadersFoldingSoftGreaterThanHard(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $headers = Headers::fromPairs([['Content-Type', 'text/plain']]);
        $headers->toFoldedString(softLimit: 100, hardLimit: 50);
    }

    public function testRuntimeExceptionIsInstanceOfExceptionInterface(): void
    {
        $exception = InvalidArgumentException::create('test');

        static::assertInstanceOf(ExceptionInterface::class, $exception);
    }
}
