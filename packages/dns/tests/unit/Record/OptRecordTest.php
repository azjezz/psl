<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\OPTRecord;
use Psl\DNS\Record\RecordType;

final class OptRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new OPTRecord('', Duration::seconds(0), 4096, 0, 0, true, []);

        static::assertSame(RecordType::OPT, $record->kind);
        static::assertSame('', $record->name);
        static::assertEquals(Duration::seconds(0), $record->duration);
        static::assertSame(4096, $record->udpPayloadSize);
        static::assertSame(0, $record->extendedRcode);
        static::assertSame(0, $record->version);
        static::assertTrue($record->dnssecOk);
        static::assertSame([], $record->options);
    }
}
