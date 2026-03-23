<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Reader;
use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Record\RecordType;
use Psl\Str;

use function strlen;

final class EncoderTest extends TestCase
{
    public function testEncodeProducesValidDnsQuery(): void
    {
        [$id, $packet] = Encoder::encode('example.com', RecordType::A);

        $reader = new Reader($packet);

        static::assertSame($id, $reader->u16());
        static::assertSame(0x0100, $reader->u16());
        static::assertSame(1, $reader->u16());
        static::assertSame(0, $reader->u16());
        static::assertSame(0, $reader->u16());
        static::assertSame(0, $reader->u16());

        static::assertSame(7, $reader->u8());
        static::assertSame('example', $reader->bytes(7));
        static::assertSame(3, $reader->u8());
        static::assertSame('com', $reader->bytes(3));
        static::assertSame(0, $reader->u8());

        static::assertSame(1, $reader->u16());
        static::assertSame(1, $reader->u16());

        static::assertTrue($reader->isConsumed());
    }

    public function testEncodeWithAaaaType(): void
    {
        [, $packet] = Encoder::encode('example.com', RecordType::AAAA);

        $reader = new Reader($packet);
        $reader->skip(12);

        $reader->skip(1 + 7);
        $reader->skip(1 + 3);
        $reader->skip(1);

        static::assertSame(28, $reader->u16());
        static::assertSame(1, $reader->u16());
    }

    public function testEncodeTransactionIdInRange(): void
    {
        [$id] = Encoder::encode('example.com', RecordType::A);

        static::assertGreaterThanOrEqual(0, $id);
        static::assertLessThanOrEqual(65_535, $id);
    }

    public function testEncodeName(): void
    {
        $encoded = Encoder::encodeName('www.example.com');

        $reader = new Reader($encoded);

        static::assertSame(3, $reader->u8());
        static::assertSame('www', $reader->bytes(3));
        static::assertSame(7, $reader->u8());
        static::assertSame('example', $reader->bytes(7));
        static::assertSame(3, $reader->u8());
        static::assertSame('com', $reader->bytes(3));
        static::assertSame(0, $reader->u8());

        static::assertTrue($reader->isConsumed());
    }

    public function testEncodeNameSingleLabel(): void
    {
        $encoded = Encoder::encodeName('localhost');

        $reader = new Reader($encoded);

        static::assertSame(9, $reader->u8());
        static::assertSame('localhost', $reader->bytes(9));
        static::assertSame(0, $reader->u8());

        static::assertTrue($reader->isConsumed());
    }

    public function testEncodeWithDnssec(): void
    {
        [$id, $packet] = Encoder::encode('example.com', RecordType::A, dnssec: true, udpPayloadSize: 4096);

        $reader = new Reader($packet);

        static::assertSame($id, $reader->u16());
        static::assertSame(0x0100, $reader->u16());
        static::assertSame(1, $reader->u16());
        static::assertSame(0, $reader->u16());
        static::assertSame(0, $reader->u16());
        static::assertSame(1, $reader->u16());

        static::assertSame(7, $reader->u8());
        $reader->skip(7);
        static::assertSame(3, $reader->u8());
        $reader->skip(3);
        static::assertSame(0, $reader->u8());
        static::assertSame(1, $reader->u16());
        static::assertSame(1, $reader->u16());

        static::assertSame(0, $reader->u8());
        static::assertSame(41, $reader->u16());
        static::assertSame(4096, $reader->u16());
        static::assertSame(0x0000_8000, $reader->u32());
        static::assertSame(0, $reader->u16());

        static::assertTrue($reader->isConsumed());
    }

    public function testEncodeWithoutDnssec(): void
    {
        [, $packet] = Encoder::encode('example.com', RecordType::A, dnssec: false);

        $reader = new Reader($packet);
        $reader->skip(2);
        $reader->skip(2);
        static::assertSame(1, $reader->u16());
        static::assertSame(0, $reader->u16());
        static::assertSame(0, $reader->u16());
        static::assertSame(0, $reader->u16());
    }

    public function testEncodeCustomUdpPayloadSize(): void
    {
        [, $packet] = Encoder::encode('example.com', RecordType::A, dnssec: true, udpPayloadSize: 1232);

        $reader = new Reader($packet);
        $reader->skip(12);

        $reader->skip(1 + 7);
        $reader->skip(1 + 3);
        $reader->skip(1);
        $reader->skip(4);

        $reader->skip(1);
        $reader->skip(2);
        static::assertSame(1232, $reader->u16());
    }

    public function testEncodeNameRootDomain(): void
    {
        static::assertSame("\x00", Encoder::encodeName(''));
        static::assertSame("\x00", Encoder::encodeName('.'));
    }

    public function testEncodeNameLabelTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Encoder::encodeName(Str\repeat('a', 64) . '.com');
    }

    public function testEncodeNameRejectsNullByteInLabel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Encoder::encodeName("exam\x00ple.com");
    }

    public function testEncodeNameTotalTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $label = Str\repeat('a', 63);
        Encoder::encodeName($label . '.' . $label . '.' . $label . '.' . $label . '.com');
    }

    public function testEncodeNameWithUnicodeLabel(): void
    {
        $encoded = Encoder::encodeName('münchen.de');

        $reader = new Reader($encoded);

        $aceLabel = 'xn--mnchen-3ya';
        static::assertSame(strlen($aceLabel), $reader->u8());
        static::assertSame($aceLabel, $reader->bytes(strlen($aceLabel)));
        static::assertSame(2, $reader->u8());
        static::assertSame('de', $reader->bytes(2));
        static::assertSame(0, $reader->u8());

        static::assertTrue($reader->isConsumed());
    }

    public function testEncodeNameWithMultipleUnicodeLabels(): void
    {
        $encoded = Encoder::encodeName('日本語.jp');

        $reader = new Reader($encoded);

        $aceLabel = 'xn--wgv71a119e';
        static::assertSame(strlen($aceLabel), $reader->u8());
        static::assertSame($aceLabel, $reader->bytes(strlen($aceLabel)));
        static::assertSame(2, $reader->u8());
        static::assertSame('jp', $reader->bytes(2));
        static::assertSame(0, $reader->u8());

        static::assertTrue($reader->isConsumed());
    }

    public function testEncodeNameAsciiUnchanged(): void
    {
        $withUnicode = Encoder::encodeName('example.com');
        $reader = new Reader($withUnicode);

        static::assertSame(7, $reader->u8());
        static::assertSame('example', $reader->bytes(7));
        static::assertSame(3, $reader->u8());
        static::assertSame('com', $reader->bytes(3));
        static::assertSame(0, $reader->u8());
    }
}
