<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\NSRecord;
use Psl\DNS\Record\RecordType;

final class NsRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new NSRecord('example.com', Duration::seconds(172_800), 'ns1.example.com');

        static::assertSame(RecordType::NS, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(172_800), $record->duration);
        static::assertSame('ns1.example.com', $record->host);
    }
}
