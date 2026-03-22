<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Exception\AuthenticationException;
use Psl\SMTP\Exception\ConnectionException;
use Psl\SMTP\Exception\ExceptionInterface;
use Psl\SMTP\Exception\ProtocolException;
use Psl\SMTP\Exception\RuntimeException;
use Psl\SMTP\Exception\TimeoutException;
use Psl\SMTP\Exception\TransmissionException;
use RuntimeException as PhpRuntimeException;

final class RuntimeExceptionTest extends TestCase
{
    public function testConnectionExceptionExtendsRuntimeException(): void
    {
        $e = ConnectionException::forConnectionFailed('host', 25);

        static::assertInstanceOf(RuntimeException::class, $e);
        static::assertInstanceOf(ExceptionInterface::class, $e);
        static::assertInstanceOf(\Psl\Exception\RuntimeException::class, $e);
    }

    public function testProtocolExceptionExtendsRuntimeException(): void
    {
        $e = ProtocolException::forMalformedResponse('bad');

        static::assertInstanceOf(RuntimeException::class, $e);
        static::assertInstanceOf(ExceptionInterface::class, $e);
    }

    public function testAuthenticationExceptionExtendsRuntimeException(): void
    {
        $e = AuthenticationException::forUnsupportedMechanism('PLAIN');

        static::assertInstanceOf(RuntimeException::class, $e);
        static::assertInstanceOf(ExceptionInterface::class, $e);
    }

    public function testTransmissionExceptionExtendsRuntimeException(): void
    {
        $e = TransmissionException::forSenderRejected('user@test.com', 550, 'Rejected');

        static::assertInstanceOf(RuntimeException::class, $e);
        static::assertInstanceOf(ExceptionInterface::class, $e);
    }

    public function testTimeoutExceptionExtendsRuntimeException(): void
    {
        $e = TimeoutException::forConnection('host', 25);

        static::assertInstanceOf(RuntimeException::class, $e);
        static::assertInstanceOf(ExceptionInterface::class, $e);
    }

    public function testExceptionCodeIsZero(): void
    {
        $e = ConnectionException::forConnectionFailed('host', 25);

        static::assertSame(0, $e->getCode());
    }

    public function testPreviousExceptionChaining(): void
    {
        $root = new PhpRuntimeException('Root cause');
        $e = ConnectionException::forConnectionFailed('host', 25, $root);

        static::assertSame($root, $e->getPrevious());
        static::assertSame('Root cause', $e->getPrevious()->getMessage());
    }

    public function testPreviousExceptionChainingForTimeout(): void
    {
        $root = new PhpRuntimeException('Cancelled');
        $e = TimeoutException::forConnection('host', 587, $root);

        static::assertSame($root, $e->getPrevious());
    }

    public function testPreviousExceptionChainingForTLS(): void
    {
        $root = new PhpRuntimeException('Handshake failed');
        $e = ConnectionException::forTLSUpgradeFailed($root);

        static::assertSame($root, $e->getPrevious());
    }
}
