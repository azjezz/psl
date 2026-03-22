<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\SMTP\Exception\ConnectionException;
use Psl\SMTP\Exception\ProtocolException;

use function Psl\SMTP\Internal\read_greeting;

final class ReadGreetingTest extends TestCase
{
    public function testValidGreeting(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220 mail.example.com ESMTP\r\n"));

        $response = read_greeting($reader);

        static::assertSame(220, $response->code);
        static::assertSame('mail.example.com ESMTP', $response->message);
    }

    public function testMultilineGreeting(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220-mail.example.com ESMTP\r\n220 Ready\r\n"));

        $response = read_greeting($reader);

        static::assertSame(220, $response->code);
        static::assertStringContainsString('Ready', $response->message);
    }

    public function testRejectedGreeting(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("554 Service unavailable\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testTemporaryFailureGreeting(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("421 Try again later\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testMalformedGreeting(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("GARBAGE\r\n"));

        $this->expectException(ProtocolException::class);

        read_greeting($reader);
    }

    public function testEmptyStreamGreeting(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle(''));

        $this->expectException(ProtocolException::class);

        read_greeting($reader);
    }

    public function testGreeting220WithEnhancedStatus(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220 2.0.0 mail.example.com ESMTP\r\n"));

        $response = read_greeting($reader);

        static::assertSame(220, $response->code);
        static::assertNotNull($response->enhancedStatus);
        static::assertSame(2, $response->enhancedStatus->class);
    }

    public function testGreeting421(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("421 Service temporarily unavailable\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testGreeting451(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("451 Requested action aborted\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testGreeting550(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("550 Access denied\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testGreetingCode250(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("250 Not a greeting\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testGreetingCode354(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("354 Not a greeting\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testGreetingCode500(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("500 Error\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testGreetingCode100(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("100 Continue\r\n"));

        $this->expectException(ConnectionException::class);

        read_greeting($reader);
    }

    public function testMultilineGreetingContainsFullMessage(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220-mail.example.com\r\n220-Welcome\r\n220 Service ready\r\n"));

        $response = read_greeting($reader);

        static::assertSame(220, $response->code);
        static::assertStringContainsString('mail.example.com', $response->message);
        static::assertStringContainsString('Welcome', $response->message);
        static::assertStringContainsString('Service ready', $response->message);
    }

    public function testGreetingReturnValue(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("220 mail.test.com ESMTP Postfix\r\n"));

        $response = read_greeting($reader);

        static::assertSame(220, $response->code);
        static::assertSame('mail.test.com ESMTP Postfix', $response->message);
        static::assertTrue($response->isPositiveCompletion());
        static::assertTrue($response->isConnectionsCategory());
    }

    public function testMalformedGreetingTooShort(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("AB\r\n"));

        $this->expectException(ProtocolException::class);

        read_greeting($reader);
    }

    public function testMalformedGreetingNonNumeric(): void
    {
        $reader = new IO\Reader(new IO\MemoryHandle("XYZ Service\r\n"));

        $this->expectException(ProtocolException::class);

        read_greeting($reader);
    }
}
