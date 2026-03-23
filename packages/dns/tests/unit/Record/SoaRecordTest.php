<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\SOARecord;

final class SoaRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new SOARecord(
            'example.com',
            Duration::seconds(3600),
            'ns1.example.com',
            'admin.example.com',
            2_024_010_101,
            Duration::seconds(7200),
            Duration::seconds(3600),
            Duration::seconds(1_209_600),
            Duration::seconds(86_400),
        );

        static::assertSame(RecordType::SOA, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame('ns1.example.com', $record->masterName);
        static::assertSame('admin.example.com', $record->responsibleName);
        static::assertSame(2_024_010_101, $record->serial);
        static::assertEquals(Duration::seconds(7200), $record->refresh);
        static::assertEquals(Duration::seconds(3600), $record->retry);
        static::assertEquals(Duration::seconds(1_209_600), $record->expire);
        static::assertEquals(Duration::seconds(86_400), $record->minimumTtl);
    }
}
