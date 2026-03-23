<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\RecordType;
use Psl\IP\Address;

final class AaaaRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new AAAARecord(
            'example.com',
            Duration::seconds(300),
            Address::v6('2606:2800:220:1:248:1893:25c8:1946'),
        );

        static::assertSame(RecordType::AAAA, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(300), $record->duration);
        static::assertSame('2606:2800:220:1:248:1893:25c8:1946', $record->address->toString());
    }
}
