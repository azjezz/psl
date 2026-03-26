<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Exception\EncodingException;
use RuntimeException;

final class EncodingExceptionTest extends TestCase
{
    public function testForInvalidDecodingInput(): void
    {
        $exception = EncodingException::forInvalidDecodingInput('bad base64 data');

        static::assertStringContainsString('bad base64 data', $exception->getMessage());
        static::assertStringContainsString('Failed to decode', $exception->getMessage());
        static::assertNull($exception->getPrevious());
    }

    public function testForDecodingFailure(): void
    {
        $previous = new RuntimeException('stream error');
        $exception = EncodingException::forDecodingFailure($previous);

        static::assertStringContainsString('stream error', $exception->getMessage());
        static::assertStringContainsString('Failed to decode', $exception->getMessage());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testForStreamFailure(): void
    {
        $previous = new RuntimeException('io failure');
        $exception = EncodingException::forStreamFailure($previous);

        static::assertStringContainsString('io failure', $exception->getMessage());
        static::assertStringContainsString('Stream encoding/decoding failure', $exception->getMessage());
        static::assertSame($previous, $exception->getPrevious());
    }
}
