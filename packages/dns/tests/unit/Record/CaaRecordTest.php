<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\CAARecord;
use Psl\DNS\Record\RecordType;

final class CaaRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new CAARecord('example.com', Duration::seconds(3600), 0, 'issue', 'letsencrypt.org');

        static::assertSame(RecordType::CAA, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(0, $record->flags);
        static::assertSame('issue', $record->tag);
        static::assertSame('letsencrypt.org', $record->value);
    }
}
