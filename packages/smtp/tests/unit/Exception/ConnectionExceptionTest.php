<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Exception\ConnectionException;
use Psl\SMTP\Exception\ExceptionInterface;
use Psl\SMTP\Exception\RuntimeException;
use RuntimeException as PhpRuntimeException;

final class ConnectionExceptionTest extends TestCase
{
    public function testImplementsExceptionInterface(): void
    {
        $e = ConnectionException::forConnectionFailed('localhost', 25);

        static::assertInstanceOf(ExceptionInterface::class, $e);
        static::assertInstanceOf(RuntimeException::class, $e);
    }

    public function testForConnectionFailed(): void
    {
        $e = ConnectionException::forConnectionFailed('smtp.example.com', 587);

        static::assertStringContainsString('smtp.example.com', $e->getMessage());
        static::assertStringContainsString('587', $e->getMessage());
        static::assertStringContainsString('Failed to connect', $e->getMessage());
        static::assertNull($e->getPrevious());
    }

    public function testForConnectionFailedWithPreviousException(): void
    {
        $previous = new PhpRuntimeException('Connection refused');
        $e = ConnectionException::forConnectionFailed('localhost', 25, $previous);

        static::assertSame($previous, $e->getPrevious());
        static::assertStringContainsString('localhost', $e->getMessage());
        static::assertStringContainsString('25', $e->getMessage());
    }

    public function testForTLSUpgradeFailed(): void
    {
        $e = ConnectionException::forTLSUpgradeFailed();

        static::assertStringContainsString('TLS', $e->getMessage());
        static::assertStringContainsString('STARTTLS', $e->getMessage());
        static::assertNull($e->getPrevious());
    }

    public function testForTLSUpgradeFailedWithPreviousException(): void
    {
        $previous = new PhpRuntimeException('Handshake failed');
        $e = ConnectionException::forTLSUpgradeFailed($previous);

        static::assertSame($previous, $e->getPrevious());
        static::assertStringContainsString('TLS', $e->getMessage());
    }

    public function testForUnexpectedGreeting(): void
    {
        $e = ConnectionException::forUnexpectedGreeting(554, 'Service unavailable');

        static::assertStringContainsString('554', $e->getMessage());
        static::assertStringContainsString('Service unavailable', $e->getMessage());
        static::assertStringContainsString('greeting', $e->getMessage());
    }

    public function testForUnexpectedGreetingWith421(): void
    {
        $e = ConnectionException::forUnexpectedGreeting(421, 'Try again later');

        static::assertStringContainsString('421', $e->getMessage());
        static::assertStringContainsString('Try again later', $e->getMessage());
    }

    public function testForConnectionFailedVariousPorts(): void
    {
        $e25 = ConnectionException::forConnectionFailed('host', 25);
        static::assertStringContainsString('25', $e25->getMessage());

        $e465 = ConnectionException::forConnectionFailed('host', 465);
        static::assertStringContainsString('465', $e465->getMessage());

        $e587 = ConnectionException::forConnectionFailed('host', 587);
        static::assertStringContainsString('587', $e587->getMessage());
    }
}
