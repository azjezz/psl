<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Exception\ExceptionInterface;
use Psl\SMTP\Exception\RuntimeException;
use Psl\SMTP\Exception\TimeoutException;
use RuntimeException as PhpRuntimeException;

final class TimeoutExceptionTest extends TestCase
{
    public function testImplementsExceptionInterface(): void
    {
        $previous = new PhpRuntimeException('timeout');
        $e = TimeoutException::forConnection('localhost', 25, $previous);

        static::assertInstanceOf(ExceptionInterface::class, $e);
        static::assertInstanceOf(RuntimeException::class, $e);
    }

    public function testForConnection(): void
    {
        $e = TimeoutException::forConnection('smtp.example.com', 587);

        static::assertStringContainsString('smtp.example.com', $e->getMessage());
        static::assertStringContainsString('587', $e->getMessage());
        static::assertStringContainsString('timed out', $e->getMessage());
        static::assertNull($e->getPrevious());
    }

    public function testForConnectionWithPrevious(): void
    {
        $previous = new PhpRuntimeException('Cancelled');
        $e = TimeoutException::forConnection('localhost', 25, $previous);

        static::assertSame($previous, $e->getPrevious());
    }

    public function testForConnectionVariousPorts(): void
    {
        $e25 = TimeoutException::forConnection('host', 25);
        static::assertStringContainsString('25', $e25->getMessage());

        $e465 = TimeoutException::forConnection('host', 465);
        static::assertStringContainsString('465', $e465->getMessage());

        $e587 = TimeoutException::forConnection('host', 587);
        static::assertStringContainsString('587', $e587->getMessage());
    }

    public function testForCommand(): void
    {
        $previous = new PhpRuntimeException('Timeout');
        $e = TimeoutException::forCommand('EHLO', $previous);

        static::assertStringContainsString('EHLO', $e->getMessage());
        static::assertStringContainsString('timed out', $e->getMessage());
        static::assertSame($previous, $e->getPrevious());
    }

    public function testForCommandVariousCommands(): void
    {
        $previous = new PhpRuntimeException('timeout');

        $eData = TimeoutException::forCommand('DATA', $previous);
        static::assertStringContainsString('DATA', $eData->getMessage());

        $eRcpt = TimeoutException::forCommand('RCPT TO', $previous);
        static::assertStringContainsString('RCPT TO', $eRcpt->getMessage());

        $eQuit = TimeoutException::forCommand('QUIT', $previous);
        static::assertStringContainsString('QUIT', $eQuit->getMessage());
    }
}
