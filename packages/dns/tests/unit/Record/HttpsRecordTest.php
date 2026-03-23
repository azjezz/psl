<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\HTTPSRecord;
use Psl\DNS\Record\RecordType;

final class HttpsRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $params = [1 => "\x00\x03h2\x00\x02h3"];
        $record = new HTTPSRecord('example.com', Duration::seconds(300), 1, 'cdn.example.com', $params);

        static::assertSame(RecordType::HTTPS, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(300), $record->duration);
        static::assertSame(1, $record->priority);
        static::assertSame('cdn.example.com', $record->target);
        static::assertSame($params, $record->params);
    }
}
