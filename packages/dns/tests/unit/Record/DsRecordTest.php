<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\DNSSEC\DigestAlgorithm;
use Psl\DNS\Record\DSRecord;
use Psl\DNS\Record\RecordType;

final class DsRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new DSRecord(
            'example.com',
            Duration::seconds(3600),
            12_345,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            'aabbccdd',
        );

        static::assertSame(RecordType::DS, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(12_345, $record->keyTag);
        static::assertSame(Algorithm::RSASHA256, $record->algorithm);
        static::assertSame(DigestAlgorithm::SHA256, $record->digestType);
        static::assertSame('aabbccdd', $record->digest);
    }
}
