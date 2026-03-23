<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\RRSIGRecord;

final class RrsigRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $signature = "\xDE\xAD\xBE\xEF";
        $record = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::A,
            Algorithm::RSASHA256,
            2,
            300,
            1_700_000_000,
            1_699_900_000,
            12_345,
            'example.com',
            $signature,
        );

        static::assertSame(RecordType::RRSIG, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(RecordType::A, $record->typeCovered);
        static::assertSame(Algorithm::RSASHA256, $record->algorithm);
        static::assertSame(2, $record->labels);
        static::assertSame(300, $record->originalTtl);
        static::assertSame(1_700_000_000, $record->expiration);
        static::assertSame(1_699_900_000, $record->inception);
        static::assertSame(12_345, $record->keyTag);
        static::assertSame('example.com', $record->signer);
        static::assertSame($signature, $record->signature);
    }
}
