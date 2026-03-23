<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\LOCRecord;
use Psl\DNS\Record\RecordType;

final class LocRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new LOCRecord(
            'example.com',
            Duration::seconds(3600),
            0,
            2_335_463_649,
            2_223_127_832,
            10_010_000,
            0x12,
            0x12,
            0x12,
        );

        static::assertSame(RecordType::LOC, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(0, $record->version);
        static::assertSame(2_335_463_649, $record->latitudeRaw);
        static::assertSame(2_223_127_832, $record->longitudeRaw);
        static::assertSame(10_010_000, $record->altitudeRaw);
        static::assertSame(0x12, $record->sizeRaw);
        static::assertSame(0x12, $record->horizontalPrecisionRaw);
        static::assertSame(0x12, $record->verticalPrecisionRaw);
        static::assertEqualsWithDelta(52.216_669, $record->latitude, 0.001);
        static::assertEqualsWithDelta(21.012_273, $record->longitude, 0.001);
        static::assertEqualsWithDelta(100.0, $record->altitude, 0.01);
        static::assertEqualsWithDelta(1.0, $record->size, 0.01);
        static::assertEqualsWithDelta(1.0, $record->horizontalPrecision, 0.01);
        static::assertEqualsWithDelta(1.0, $record->verticalPrecision, 0.01);
    }
}
