<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\SVCBRecord;

final class SvcbRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $params = [1 => "\x00\x03h2\x00\x02h3", 3 => "\x00\x50"];
        $record = new SVCBRecord('example.com', Duration::seconds(300), 1, 'svc.example.com', $params);

        static::assertSame(RecordType::SVCB, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(300), $record->duration);
        static::assertSame(1, $record->priority);
        static::assertSame('svc.example.com', $record->target);
        static::assertSame($params, $record->params);
    }
}
