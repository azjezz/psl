<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;
use Psl\IP\Address;

final class ARecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new ARecord('example.com', Duration::seconds(300), Address::v4('93.184.216.34'));

        static::assertSame(RecordType::A, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(300), $record->duration);
        static::assertSame('93.184.216.34', $record->address->toString());
    }
}
