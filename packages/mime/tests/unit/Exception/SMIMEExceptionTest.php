<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Exception\SMIMEException;
use RuntimeException;

final class SMIMEExceptionTest extends TestCase
{
    public function testForSigningFailure(): void
    {
        $exception = SMIMEException::forSigningFailure();

        static::assertStringContainsString('S/MIME signing failed', $exception->getMessage());
        static::assertNull($exception->getPrevious());
    }

    public function testForSigningFailureWithPrevious(): void
    {
        $previous = new RuntimeException('key error');
        $exception = SMIMEException::forSigningFailure($previous);

        static::assertStringContainsString('S/MIME signing failed', $exception->getMessage());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testForVerificationFailure(): void
    {
        $exception = SMIMEException::forVerificationFailure();

        static::assertStringContainsString('S/MIME verification failed', $exception->getMessage());
        static::assertNull($exception->getPrevious());
    }

    public function testForVerificationFailureWithPrevious(): void
    {
        $previous = new RuntimeException('cert error');
        $exception = SMIMEException::forVerificationFailure($previous);

        static::assertStringContainsString('S/MIME verification failed', $exception->getMessage());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testForEncryptionFailure(): void
    {
        $exception = SMIMEException::forEncryptionFailure();

        static::assertStringContainsString('S/MIME encryption failed', $exception->getMessage());
        static::assertNull($exception->getPrevious());
    }

    public function testForEncryptionFailureWithPrevious(): void
    {
        $previous = new RuntimeException('cipher error');
        $exception = SMIMEException::forEncryptionFailure($previous);

        static::assertStringContainsString('S/MIME encryption failed', $exception->getMessage());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testForDecryptionFailure(): void
    {
        $exception = SMIMEException::forDecryptionFailure();

        static::assertStringContainsString('S/MIME decryption failed', $exception->getMessage());
        static::assertNull($exception->getPrevious());
    }
}
