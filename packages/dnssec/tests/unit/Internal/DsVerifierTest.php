<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\DNSSEC\DigestAlgorithm;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\DSRecord;
use Psl\DNSSEC\Internal\DS\DSVerifier;
use Psl\DNSSEC\Internal\KeyTag;
use Psl\Hash;
use Psl\Str\Byte;

final class DsVerifierTest extends TestCase
{
    public function testVerifyMatchingDsAndDnskey(): void
    {
        $publicKey = "\x01\x02\x03\x04\x05\x06\x07\x08";
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $publicKey);

        $keyTag = KeyTag::compute($dnskey);

        $ownerWire = "\x07example\x03com\x00";
        $rdata = "\x01\x01\x03\x08" . $publicKey;
        $digest = Hash\hash($ownerWire . $rdata, Hash\Algorithm::Sha256);

        $ds = new DSRecord(
            'example.com',
            Duration::seconds(3600),
            $keyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            $digest,
        );

        static::assertTrue(DSVerifier::verify($ds, $dnskey, 'example.com'));
    }

    public function testVerifyMismatchedDigest(): void
    {
        $dnskey = new DNSKEYRecord(
            'example.com',
            Duration::seconds(3600),
            257,
            3,
            Algorithm::RSASHA256,
            "\x01\x02\x03\x04",
        );

        $ds = new DSRecord(
            'example.com',
            Duration::seconds(3600),
            KeyTag::compute($dnskey),
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            'deadbeef',
        );

        static::assertFalse(DSVerifier::verify($ds, $dnskey, 'example.com'));
    }

    public function testVerifySha1Digest(): void
    {
        $publicKey = "\x01\x02\x03\x04\x05\x06\x07\x08";
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $publicKey);

        $keyTag = KeyTag::compute($dnskey);

        $ownerWire = "\x07example\x03com\x00";
        $rdata = "\x01\x01\x03\x08" . $publicKey;
        $digest = Hash\hash($ownerWire . $rdata, Hash\Algorithm::Sha1);

        $ds = new DSRecord(
            'example.com',
            Duration::seconds(3600),
            $keyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA1,
            $digest,
        );

        static::assertTrue(DSVerifier::verify($ds, $dnskey, 'example.com'));
    }

    public function testVerifyCaseInsensitiveOwnerName(): void
    {
        $publicKey = "\x01\x02\x03\x04\x05\x06\x07\x08";
        $dnskey = new DNSKEYRecord('EXAMPLE.COM', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $publicKey);

        $keyTag = KeyTag::compute($dnskey);

        $ownerWire = "\x07example\x03com\x00";
        $rdata = "\x01\x01\x03\x08" . $publicKey;
        $digest = Hash\hash($ownerWire . $rdata, Hash\Algorithm::Sha256);

        $ds = new DSRecord(
            'EXAMPLE.COM',
            Duration::seconds(3600),
            $keyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            $digest,
        );

        static::assertTrue(DSVerifier::verify($ds, $dnskey, 'EXAMPLE.COM'));
    }

    public function testVerifySha384Digest(): void
    {
        $publicKey = "\x01\x02\x03\x04\x05\x06\x07\x08";
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $publicKey);

        $keyTag = KeyTag::compute($dnskey);

        $ownerWire = "\x07example\x03com\x00";
        $rdata = "\x01\x01\x03\x08" . $publicKey;
        $digest = Hash\hash($ownerWire . $rdata, Hash\Algorithm::Sha384);

        $ds = new DSRecord(
            'example.com',
            Duration::seconds(3600),
            $keyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA384,
            $digest,
        );

        static::assertTrue(DSVerifier::verify($ds, $dnskey, 'example.com'));
    }

    public function testVerifyDigestCaseInsensitive(): void
    {
        $publicKey = "\x01\x02\x03\x04\x05\x06\x07\x08";
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $publicKey);

        $keyTag = KeyTag::compute($dnskey);

        $ownerWire = "\x07example\x03com\x00";
        $rdata = "\x01\x01\x03\x08" . $publicKey;
        $digest = Hash\hash($ownerWire . $rdata, Hash\Algorithm::Sha256);

        $upperDigest = Byte\uppercase($digest);

        $ds = new DSRecord(
            'example.com',
            Duration::seconds(3600),
            $keyTag,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            $upperDigest,
        );

        static::assertTrue(DSVerifier::verify($ds, $dnskey, 'example.com'));
    }
}
