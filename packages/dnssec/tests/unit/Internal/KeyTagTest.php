<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNSSEC\Internal\KeyTag;
use Psl\Str;

final class KeyTagTest extends TestCase
{
    public function testComputeKnownKeyTag(): void
    {
        $dnskey = new DNSKEYRecord(
            'example.com',
            Duration::seconds(3600),
            257,
            3,
            Algorithm::RSASHA256,
            Str\repeat("\x01", 32),
        );

        $keyTag = KeyTag::compute($dnskey);

        static::assertGreaterThanOrEqual(0, $keyTag);
        static::assertLessThanOrEqual(65_535, $keyTag);
    }

    public function testComputeConsistency(): void
    {
        $dnskey = new DNSKEYRecord(
            'example.com',
            Duration::seconds(3600),
            256,
            3,
            Algorithm::ECDSAP256SHA256,
            "\xAB\xCD\xEF\x01\x23\x45\x67\x89",
        );

        $tag1 = KeyTag::compute($dnskey);
        $tag2 = KeyTag::compute($dnskey);

        static::assertSame($tag1, $tag2);
    }

    public function testDifferentKeysProduceDifferentTags(): void
    {
        $dnskey1 = new DNSKEYRecord(
            'example.com',
            Duration::seconds(3600),
            257,
            3,
            Algorithm::RSASHA256,
            "\x01\x02\x03\x04",
        );
        $dnskey2 = new DNSKEYRecord(
            'example.com',
            Duration::seconds(3600),
            257,
            3,
            Algorithm::RSASHA256,
            "\x05\x06\x07\x08",
        );

        static::assertNotSame(KeyTag::compute($dnskey1), KeyTag::compute($dnskey2));
    }
}
