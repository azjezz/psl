<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Edns;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\EDNS\TCPKeepaliveOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;

final class TcpKeepaliveOptionTest extends TestCase
{
    public function testCode(): void
    {
        $option = new TCPKeepaliveOption();

        static::assertSame(11, $option->code);
    }

    public function testNullTimeout(): void
    {
        $option = new TCPKeepaliveOption();

        static::assertNull($option->timeout);
    }

    public function testWithTimeout(): void
    {
        $option = new TCPKeepaliveOption(6000);

        static::assertSame(6000, $option->timeout);
    }

    public function testToWireFormatNullTimeout(): void
    {
        $option = new TCPKeepaliveOption();

        static::assertSame('', $option->toWireFormat());
    }

    public function testToWireFormatWithTimeout(): void
    {
        $option = new TCPKeepaliveOption(6000);

        $expected = new Writer()->u16(6000)->toString();

        static::assertSame($expected, $option->toWireFormat());
    }

    public function testRoundTripNullTimeout(): void
    {
        $original = new TCPKeepaliveOption();
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $keepalive = $decoded[0];
        static::assertInstanceOf(TCPKeepaliveOption::class, $keepalive);
        static::assertNull($keepalive->timeout);
    }

    public function testRoundTripWithTimeout(): void
    {
        $original = new TCPKeepaliveOption(1500);
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $keepalive = $decoded[0];
        static::assertInstanceOf(TCPKeepaliveOption::class, $keepalive);
        static::assertSame(1500, $keepalive->timeout);
    }
}
