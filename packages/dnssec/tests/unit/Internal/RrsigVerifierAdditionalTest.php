<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use OpenSSLAsymmetricKey;
use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\DNSSEC\DigestAlgorithm;
use Psl\DNS\Internal\Base32Hex;
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Internal\TypeBitmap;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\CNAMERecord;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\DSRecord;
use Psl\DNS\Record\MXRecord;
use Psl\DNS\Record\NSEC3PARAMRecord;
use Psl\DNS\Record\NSEC3Record;
use Psl\DNS\Record\NSECRecord;
use Psl\DNS\Record\NSRecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\RRSIGRecord;
use Psl\DNS\Record\SOARecord;
use Psl\DNSSEC\Internal\KeyTag;
use Psl\DNSSEC\Internal\RRSIG\RRSIGVerifier;
use Psl\IP\Address;
use Psl\Str\Byte;
use RuntimeException;

use function hex2bin;
use function is_string;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function openssl_sign;

use const OPENSSL_ALGO_SHA256;
use const OPENSSL_KEYTYPE_RSA;

final class RrsigVerifierAdditionalTest extends TestCase
{
    public function testVerifyNsRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $nsRecord = new NSRecord('example.com', Duration::seconds(300), 'ns1.example.com');

        $rdata = Encoder::encodeName(Byte\lowercase('ns1.example.com'));

        $signedData = self::buildSignedData(RecordType::NS, $keyTag, 'example.com', 2, 300, $nsRecord->name, $rdata);

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::NS,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$nsRecord]));
    }

    public function testVerifyCnameRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $cnameRecord = new CNAMERecord('www.example.com', Duration::seconds(300), 'example.com');

        $rdata = Encoder::encodeName(Byte\lowercase('example.com'));

        $signedData = self::buildSignedData(
            RecordType::CNAME,
            $keyTag,
            'example.com',
            3,
            300,
            'www.example.com',
            $rdata,
        );

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'www.example.com',
            Duration::seconds(3600),
            RecordType::CNAME,
            Algorithm::RSASHA256,
            3,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$cnameRecord]));
    }

    public function testVerifyMxRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $mxRecord = new MXRecord('example.com', Duration::seconds(300), 10, 'mail.example.com');

        $rdata = new Writer()
            ->u16(10)
            ->bytes(Encoder::encodeName(Byte\lowercase('mail.example.com')))
            ->toString();

        $signedData = self::buildSignedData(RecordType::MX, $keyTag, 'example.com', 2, 300, 'example.com', $rdata);

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::MX,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$mxRecord]));
    }

    public function testVerifySoaRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $soaRecord = new SOARecord(
            'example.com',
            Duration::seconds(300),
            'ns1.example.com',
            'admin.example.com',
            2_024_010_101,
            Duration::seconds(3600),
            Duration::seconds(900),
            Duration::seconds(604_800),
            Duration::seconds(86_400),
        );

        $rdata =
            Encoder::encodeName(Byte\lowercase('ns1.example.com'))
            . Encoder::encodeName(Byte\lowercase('admin.example.com'))
            . new Writer()
                ->u32(2_024_010_101)
                ->u32(3600)
                ->u32(900)
                ->u32(604_800)
                ->u32(86_400)
                ->toString();

        $signedData = self::buildSignedData(RecordType::SOA, $keyTag, 'example.com', 2, 300, 'example.com', $rdata);

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::SOA,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$soaRecord]));
    }

    public function testVerifyDsRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $dsRecord = new DSRecord(
            'example.com',
            Duration::seconds(300),
            12_345,
            Algorithm::RSASHA256,
            DigestAlgorithm::SHA256,
            'aabbccdd',
        );

        $digestBytes = hex2bin('aabbccdd');
        $rdata = new Writer()
            ->u16(12_345)
            ->u8(Algorithm::RSASHA256->value)
            ->u8(DigestAlgorithm::SHA256->value)
            ->bytes($digestBytes)
            ->toString();

        $signedData = self::buildSignedData(RecordType::DS, $keyTag, 'com', 2, 300, 'example.com', $rdata);

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::DS,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$dsRecord]));
    }

    public function testVerifyDnskeyRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $innerDnskey = new DNSKEYRecord(
            'example.com',
            Duration::seconds(300),
            256,
            3,
            Algorithm::RSASHA256,
            'some-key',
        );

        $rdata = new Writer()
            ->u16(256)
            ->u8(3)
            ->u8(Algorithm::RSASHA256->value)
            ->bytes('some-key')
            ->toString();

        $signedData = self::buildSignedData(RecordType::DNSKEY, $keyTag, 'example.com', 2, 300, 'example.com', $rdata);

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::DNSKEY,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$innerDnskey]));
    }

    public function testVerifyNsecRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $nsecRecord = new NSECRecord(
            'example.com',
            Duration::seconds(300),
            'mail.example.com',
            [RecordType::A, RecordType::AAAA],
        );

        $rdata =
            Encoder::encodeName(Byte\lowercase('mail.example.com'))
            . TypeBitmap::encode([RecordType::A, RecordType::AAAA]);

        $signedData = self::buildSignedData(RecordType::NSEC, $keyTag, 'example.com', 2, 300, 'example.com', $rdata);

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::NSEC,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$nsecRecord]));
    }

    public function testVerifyNsec3RecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $nsec3Record = new NSEC3Record(
            'ABCDEF0123456789ABCDEF0123456789.example.com',
            Duration::seconds(300),
            1,
            0,
            5,
            'aabb',
            'FEDCBA9876543210FEDCBA9876543210',
            [RecordType::A, RecordType::AAAA],
        );

        $saltBytes = hex2bin('aabb');
        $nextHashBytes = Base32Hex::decode('FEDCBA9876543210FEDCBA9876543210');

        $rdata = new Writer()
            ->u8(1)
            ->u8(0)
            ->u16(5)
            ->u8(Byte\length($saltBytes))
            ->bytes($saltBytes)
            ->u8(Byte\length($nextHashBytes))
            ->bytes($nextHashBytes)
            ->bytes(TypeBitmap::encode([RecordType::A, RecordType::AAAA]))
            ->toString();

        $signedData = self::buildSignedData(
            RecordType::NSEC3,
            $keyTag,
            'example.com',
            3,
            300,
            'abcdef0123456789abcdef0123456789.example.com',
            $rdata,
        );

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'ABCDEF0123456789ABCDEF0123456789.example.com',
            Duration::seconds(3600),
            RecordType::NSEC3,
            Algorithm::RSASHA256,
            3,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$nsec3Record]));
    }

    public function testVerifyNsec3ParamRecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $nsec3paramRecord = new NSEC3PARAMRecord('example.com', Duration::seconds(300), 1, 0, 10, 'aabb');

        $saltBytes = hex2bin('aabb');
        $rdata = new Writer()
            ->u8(1)
            ->u8(0)
            ->u16(10)
            ->u8(Byte\length($saltBytes))
            ->bytes($saltBytes)
            ->toString();

        $signedData = self::buildSignedData(
            RecordType::NSEC3PARAM,
            $keyTag,
            'example.com',
            2,
            300,
            'example.com',
            $rdata,
        );

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::NSEC3PARAM,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$nsec3paramRecord]));
    }

    public function testVerifyAAAARecordRdataEncoding(): void
    {
        [$rawKey, $key] = self::generateRsaKey();

        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, $rawKey);
        $keyTag = KeyTag::compute($dnskey);

        $aaaaRecord = new AAAARecord('example.com', Duration::seconds(300), Address::v6('2001:db8::1'));

        $rdata = $aaaaRecord->address->toBytes();

        $signedData = self::buildSignedData(RecordType::AAAA, $keyTag, 'example.com', 2, 300, 'example.com', $rdata);

        $signature = null;
        openssl_sign($signedData, $signature, $key, OPENSSL_ALGO_SHA256);

        $now = Timestamp::now()->getSeconds();
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::AAAA,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            $keyTag,
            'example.com',
            $signature,
        );

        static::assertTrue(RRSIGVerifier::verify($rrsig, $dnskey, [$aaaaRecord]));
    }

    public function testIsWithinTimeWindowReturnsTrueForCurrentTime(): void
    {
        $now = Timestamp::now()->getSeconds();

        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::A,
            Algorithm::RSASHA256,
            2,
            300,
            $now + 86_400,
            $now - 86_400,
            12_345,
            'example.com',
            'sig',
        );

        static::assertTrue(RRSIGVerifier::isWithinTimeWindow($rrsig));
    }

    public function testIsWithinTimeWindowReturnsFalseForExpired(): void
    {
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::A,
            Algorithm::RSASHA256,
            2,
            300,
            1_600_000_000,
            1_500_000_000,
            12_345,
            'example.com',
            'sig',
        );

        static::assertFalse(RRSIGVerifier::isWithinTimeWindow($rrsig));
    }

    public function testIsWithinTimeWindowReturnsFalseForNotYetValid(): void
    {
        $rrsig = new RRSIGRecord(
            'example.com',
            Duration::seconds(3600),
            RecordType::A,
            Algorithm::RSASHA256,
            2,
            300,
            3_000_000_000,
            2_900_000_000,
            12_345,
            'example.com',
            'sig',
        );

        static::assertFalse(RRSIGVerifier::isWithinTimeWindow($rrsig));
    }

    private static function buildSignedData(
        RecordType $type,
        int $keyTag,
        string $signer,
        int $labels,
        int $originalTtl,
        string $ownerName,
        string $rdata,
    ): string {
        $now = Timestamp::now()->getSeconds();
        $expiration = $now + 86_400;
        $inception = $now - 86_400;

        $signerWire = Encoder::encodeName($signer);
        $rrsigPrefix = new Writer()
            ->u16($type->value)
            ->u8(8)
            ->u8($labels)
            ->u32($originalTtl)
            ->u32($expiration)
            ->u32($inception)
            ->u16($keyTag)
            ->bytes($signerWire)
            ->toString();

        $ownerWire = Encoder::encodeName(Byte\lowercase($ownerName));
        $canonicalRr = new Writer()
            ->bytes($ownerWire)
            ->u16($type->value)
            ->u16(1)
            ->u32($originalTtl)
            ->u16(Byte\length($rdata))
            ->bytes($rdata)
            ->toString();

        return $rrsigPrefix . $canonicalRr;
    }

    /** @return array{string, OpenSSLAsymmetricKey} */
    private static function generateRsaKey(): array
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($key === false) {
            throw new RuntimeException('Failed to generate RSA key pair');
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false) {
            throw new RuntimeException('Failed to get RSA key details');
        }

        if (!isset($details['rsa']['e']) || !is_string($details['rsa']['e'])) {
            throw new RuntimeException('Missing RSA exponent in key details');
        }

        if (!isset($details['rsa']['n']) || !is_string($details['rsa']['n'])) {
            throw new RuntimeException('Missing RSA modulus in key details');
        }

        $exponent = $details['rsa']['e'];
        $modulus = $details['rsa']['n'];
        $expLen = Byte\length($exponent);

        if ($expLen < 256) {
            return [Byte\chr($expLen) . $exponent . $modulus, $key];
        }

        return ["\x00" . Byte\chr(($expLen >> 8) & 0xFF) . Byte\chr($expLen & 0xFF) . $exponent . $modulus, $key];
    }
}
