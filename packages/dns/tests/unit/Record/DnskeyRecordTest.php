<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\RecordType;

final class DnskeyRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $publicKey = "\x01\x02\x03\x04";
        $record = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $publicKey);

        static::assertSame(RecordType::DNSKEY, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(257, $record->flags);
        static::assertSame(3, $record->protocol);
        static::assertSame(Algorithm::RSASHA256, $record->algorithm);
        static::assertSame($publicKey, $record->publicKey);
    }
}
