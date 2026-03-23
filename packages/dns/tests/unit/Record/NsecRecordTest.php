<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\NSECRecord;
use Psl\DNS\Record\RecordType;

final class NsecRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $types = [RecordType::A, RecordType::AAAA, RecordType::RRSIG];
        $record = new NSECRecord('example.com', Duration::seconds(3600), 'www.example.com', $types);

        static::assertSame(RecordType::NSEC, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame('www.example.com', $record->nextDomainName);
        static::assertSame($types, $record->types);
    }
}
