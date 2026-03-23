<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\MXRecord;
use Psl\DNS\Record\RecordType;

final class MxRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new MXRecord('example.com', Duration::seconds(3600), 10, 'mail.example.com');

        static::assertSame(RecordType::MX, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(10, $record->preference);
        static::assertSame('mail.example.com', $record->exchange);
    }
}
