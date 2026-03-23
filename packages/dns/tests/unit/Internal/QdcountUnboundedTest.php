<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Internal\Decoder;

final class QdcountUnboundedTest extends TestCase
{
    public function testDecodesPacketWithZeroQuestions(): void
    {
        $packet = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(0)
            ->u16(0)
            ->u16(0)
            ->u16(0)
            ->toString();

        $response = Decoder::decode($packet);

        static::assertSame(0x1234, $response->id);
    }

    public function testDecodesPacketWithOneQuestion(): void
    {
        $packet = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(1)
            ->u16(0)
            ->u16(0)
            ->u16(0)
            ->toString();

        $packet .= "\x07example\x03com\x00";
        $packet .= new Writer()
            ->u16(1)
            ->u16(1)
            ->toString();

        $response = Decoder::decode($packet);

        static::assertSame(0x1234, $response->id);
    }

    public function testRejectsExcessiveQdcount(): void
    {
        $packet = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(4097)
            ->u16(0)
            ->u16(0)
            ->u16(0)
            ->toString();

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('exceeding the limit of');

        Decoder::decode($packet);
    }

    public function testRejectsMaxQdcount(): void
    {
        $packet = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(65_535)
            ->u16(0)
            ->u16(0)
            ->u16(0)
            ->toString();

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('exceeding the limit of');

        Decoder::decode($packet);
    }
}
