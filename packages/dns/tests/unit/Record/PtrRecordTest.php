<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\PTRRecord;
use Psl\DNS\Record\RecordType;

final class PtrRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new PTRRecord('34.216.184.93.in-addr.arpa', Duration::seconds(3600), 'example.com');

        static::assertSame(RecordType::PTR, $record->kind);
        static::assertSame('34.216.184.93.in-addr.arpa', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame('example.com', $record->target);
    }
}
