<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Internal\TypeBitmap;
use Psl\DNS\Record\RecordType;

use function str_repeat;
use function strlen;

final class TypeBitmapTest extends TestCase
{
    public function testDecodeAAndAaaa(): void
    {
        $data = "\x00\x04\x40\x00\x00\x08";
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 6);

        static::assertCount(2, $types);
        static::assertSame(RecordType::A, $types[0]);
        static::assertSame(RecordType::AAAA, $types[1]);
    }

    public function testDecodeMultipleTypes(): void
    {
        $data = "\x00\x07\x62\x01\x80\x08\x00\x03\x80";
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 9);

        static::assertCount(9, $types);
        static::assertSame(RecordType::A, $types[0]);
        static::assertSame(RecordType::NS, $types[1]);
        static::assertSame(RecordType::SOA, $types[2]);
        static::assertSame(RecordType::MX, $types[3]);
        static::assertSame(RecordType::TXT, $types[4]);
        static::assertSame(RecordType::AAAA, $types[5]);
        static::assertSame(RecordType::RRSIG, $types[6]);
        static::assertSame(RecordType::NSEC, $types[7]);
        static::assertSame(RecordType::DNSKEY, $types[8]);
    }

    public function testDecodeEmptyBitmap(): void
    {
        $data = '';
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 0);

        static::assertSame([], $types);
    }

    public function testDecodeUnknownTypesSkipped(): void
    {
        $data = "\x00\x01\x80";
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 3);

        static::assertSame([], $types);
    }

    public function testDecodeHighWindow(): void
    {
        $data = "\x01\x01\x40";
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 3);

        static::assertCount(1, $types);
        static::assertSame(RecordType::CAA, $types[0]);
    }

    public function testDecodeMaxWindowNumberSkipsUnknownTypes(): void
    {
        $data = "\xFF\x01\x40";
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 3);

        static::assertSame([], $types);
    }

    public function testDecodeMultipleWindows(): void
    {
        $data = "\x00\x01\x40\x01\x01\x40";
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 6);

        static::assertCount(2, $types);
        static::assertSame(RecordType::A, $types[0]);
        static::assertSame(RecordType::CAA, $types[1]);
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $types = [RecordType::A, RecordType::AAAA, RecordType::NS, RecordType::MX, RecordType::RRSIG, RecordType::NSEC];

        $encoded = TypeBitmap::encode($types);
        $offset = 0;
        $decoded = TypeBitmap::decodeRaw($encoded, $offset, strlen($encoded), strlen($encoded));

        static::assertCount(6, $decoded);
        static::assertSame(RecordType::A, $decoded[0]);
        static::assertSame(RecordType::NS, $decoded[1]);
        static::assertSame(RecordType::MX, $decoded[2]);
        static::assertSame(RecordType::AAAA, $decoded[3]);
        static::assertSame(RecordType::RRSIG, $decoded[4]);
        static::assertSame(RecordType::NSEC, $decoded[5]);
    }

    public function testDecodeNonSequentialWindowsWithGap(): void
    {
        $data = "\x00\x01\x40\x05\x01\x40";
        $offset = 0;

        $types = TypeBitmap::decodeRaw($data, $offset, strlen($data), 6);

        static::assertCount(1, $types);
        static::assertSame(RecordType::A, $types[0]);
    }

    public function testDecodeBitmapLengthZeroThrows(): void
    {
        $data = "\x00\x00";
        $offset = 0;

        $this->expectException(InvalidArgumentException::class);

        TypeBitmap::decodeRaw($data, $offset, strlen($data), 2);
    }

    public function testDecodeBitmapLengthExceeds32Throws(): void
    {
        $data = "\x00\x21" . str_repeat("\x00", 33);
        $offset = 0;

        $this->expectException(InvalidArgumentException::class);

        TypeBitmap::decodeRaw($data, $offset, strlen($data), 35);
    }
}
