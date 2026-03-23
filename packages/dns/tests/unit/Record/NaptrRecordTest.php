<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\NAPTRRecord;
use Psl\DNS\Record\RecordType;

final class NaptrRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new NAPTRRecord(
            'example.com',
            Duration::seconds(3600),
            100,
            10,
            's',
            'SIP+D2U',
            '',
            '_sip._udp.example.com',
        );

        static::assertSame(RecordType::NAPTR, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(100, $record->order);
        static::assertSame(10, $record->preference);
        static::assertSame('s', $record->flags);
        static::assertSame('SIP+D2U', $record->services);
        static::assertSame('', $record->regexp);
        static::assertSame('_sip._udp.example.com', $record->replacement);
    }
}
