<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\SRVRecord;

final class SrvRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new SRVRecord('_sip._tcp.example.com', Duration::seconds(86_400), 10, 60, 5060, 'sip.example.com');

        static::assertSame(RecordType::SRV, $record->kind);
        static::assertSame('_sip._tcp.example.com', $record->name);
        static::assertEquals(Duration::seconds(86_400), $record->duration);
        static::assertSame(10, $record->priority);
        static::assertSame(60, $record->weight);
        static::assertSame(5060, $record->port);
        static::assertSame('sip.example.com', $record->target);
    }
}
