<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\TLSA\CertificateUsage;
use Psl\DNS\Record\TLSA\MatchingType;
use Psl\DNS\Record\TLSA\Selector;
use Psl\DNS\Record\TLSARecord;

final class TlsaRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new TLSARecord(
            '_443._tcp.example.com',
            Duration::seconds(3600),
            CertificateUsage::DANE_EE,
            Selector::SubjectPublicKeyInfo,
            MatchingType::SHA256,
            'aabbccdd',
        );

        static::assertSame(RecordType::TLSA, $record->kind);
        static::assertSame('_443._tcp.example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(CertificateUsage::DANE_EE, $record->certificateUsage);
        static::assertSame(Selector::SubjectPublicKeyInfo, $record->selector);
        static::assertSame(MatchingType::SHA256, $record->matchingType);
        static::assertSame('aabbccdd', $record->certificateAssociationData);
    }
}
