<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DNS\Record\RecordType;

final class RecordTypeTest extends TestCase
{
    public function testAllCasesHaveExpectedValues(): void
    {
        static::assertSame(1, RecordType::A->value);
        static::assertSame(2, RecordType::NS->value);
        static::assertSame(5, RecordType::CNAME->value);
        static::assertSame(6, RecordType::SOA->value);
        static::assertSame(12, RecordType::PTR->value);
        static::assertSame(15, RecordType::MX->value);
        static::assertSame(16, RecordType::TXT->value);
        static::assertSame(28, RecordType::AAAA->value);
        static::assertSame(29, RecordType::LOC->value);
        static::assertSame(33, RecordType::SRV->value);
        static::assertSame(35, RecordType::NAPTR->value);
        static::assertSame(41, RecordType::OPT->value);
        static::assertSame(43, RecordType::DS->value);
        static::assertSame(44, RecordType::SSHFP->value);
        static::assertSame(46, RecordType::RRSIG->value);
        static::assertSame(47, RecordType::NSEC->value);
        static::assertSame(48, RecordType::DNSKEY->value);
        static::assertSame(50, RecordType::NSEC3->value);
        static::assertSame(51, RecordType::NSEC3PARAM->value);
        static::assertSame(52, RecordType::TLSA->value);
        static::assertSame(64, RecordType::SVCB->value);
        static::assertSame(65, RecordType::HTTPS->value);
        static::assertSame(257, RecordType::CAA->value);
    }
}
