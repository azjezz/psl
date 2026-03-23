<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNS\Internal\TypeBitmap;
use Psl\DNS\Record\RecordType;

use function strlen;

final class TypeBitmapEncodeTest extends TestCase
{
    public function testRoundTrip(): void
    {
        $types = [RecordType::A, RecordType::MX, RecordType::TXT, RecordType::AAAA];

        $encoded = TypeBitmap::encode($types);
        $offset = 0;
        $decoded = TypeBitmap::decodeRaw($encoded, $offset, strlen($encoded), strlen($encoded));

        static::assertSame($types, $decoded);
    }

    public function testEmptyList(): void
    {
        $encoded = TypeBitmap::encode([]);

        static::assertSame('', $encoded);
    }

    public function testMultipleWindows(): void
    {
        $types = [RecordType::A, RecordType::CAA];

        $encoded = TypeBitmap::encode($types);
        $offset = 0;
        $decoded = TypeBitmap::decodeRaw($encoded, $offset, strlen($encoded), strlen($encoded));

        static::assertSame($types, $decoded);
    }

    public function testSingleType(): void
    {
        $types = [RecordType::AAAA];

        $encoded = TypeBitmap::encode($types);
        $offset = 0;
        $decoded = TypeBitmap::decodeRaw($encoded, $offset, strlen($encoded), strlen($encoded));

        static::assertSame($types, $decoded);
    }
}
