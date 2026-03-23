<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\CNAMERecord;
use Psl\DNS\Record\RecordType;

final class CnameRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new CNAMERecord('www.example.com', Duration::seconds(600), 'example.com');

        static::assertSame(RecordType::CNAME, $record->kind);
        static::assertSame('www.example.com', $record->name);
        static::assertEquals(Duration::seconds(600), $record->duration);
        static::assertSame('example.com', $record->target);
    }
}
