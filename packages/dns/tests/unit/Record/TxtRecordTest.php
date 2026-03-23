<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\TXTRecord;

final class TxtRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new TXTRecord('example.com', Duration::seconds(300), ['v=spf1 include:_spf.example.com ~all']);

        static::assertSame(RecordType::TXT, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(300), $record->duration);
        static::assertSame(['v=spf1 include:_spf.example.com ~all'], $record->strings);
        static::assertSame('v=spf1 include:_spf.example.com ~all', $record->data);
    }

    public function testMultipleStrings(): void
    {
        $record = new TXTRecord('example.com', Duration::seconds(300), ['hello', ' world']);

        static::assertSame(['hello', ' world'], $record->strings);
        static::assertSame('hello world', $record->data);
    }
}
