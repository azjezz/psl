<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Exception\ExceptionInterface;
use Psl\SMTP\Exception\ProtocolException;
use Psl\SMTP\Exception\RuntimeException;

final class ProtocolExceptionTest extends TestCase
{
    public function testImplementsExceptionInterface(): void
    {
        $e = ProtocolException::forMalformedResponse('bad');

        static::assertInstanceOf(ExceptionInterface::class, $e);
        static::assertInstanceOf(RuntimeException::class, $e);
    }

    public function testForMalformedResponse(): void
    {
        $e = ProtocolException::forMalformedResponse('XYZ Invalid');

        static::assertStringContainsString('malformed', $e->getMessage());
        static::assertStringContainsString('XYZ Invalid', $e->getMessage());
    }

    public function testForMalformedResponseEmpty(): void
    {
        $e = ProtocolException::forMalformedResponse('');

        static::assertStringContainsString('malformed', $e->getMessage());
    }

    public function testForMalformedResponseWithSpecialChars(): void
    {
        $e = ProtocolException::forMalformedResponse("250|OK\r\n");

        static::assertStringContainsString('250|OK', $e->getMessage());
    }

    public function testForUnexpectedCode(): void
    {
        $e = ProtocolException::forUnexpectedCode(250, 502, 'Command not recognized');

        static::assertStringContainsString('250', $e->getMessage());
        static::assertStringContainsString('502', $e->getMessage());
        static::assertStringContainsString('Command not recognized', $e->getMessage());
    }

    public function testForUnexpectedCodeDifferentCodes(): void
    {
        $e = ProtocolException::forUnexpectedCode(220, 421, 'Service not available');

        static::assertStringContainsString('220', $e->getMessage());
        static::assertStringContainsString('421', $e->getMessage());
        static::assertStringContainsString('Service not available', $e->getMessage());
    }
}
