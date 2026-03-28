<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Internal\Base32Hex;
use Psl\DNS\Record\NSEC3Record;
use Psl\DNS\Record\NSECRecord;
use Psl\DNS\Record\RecordType;
use Psl\DNSSEC\Exception\InvalidProofException;
use Psl\DNSSEC\Internal\NSEC\NSEC3Hash;
use Psl\DNSSEC\Internal\NSEC\NSECProofValidator;
use Psl\Str\Byte;

final class NsecProofValidatorTest extends TestCase
{
    public function testNsecNxdomainProof(): void
    {
        $nsec1 = new NSECRecord(
            'alpha.example.com',
            Duration::seconds(3600),
            'gamma.example.com',
            [RecordType::A, RecordType::RRSIG, RecordType::NSEC],
        );

        $nsec2 = new NSECRecord('example.com', Duration::seconds(3600), 'alpha.example.com', [
            RecordType::A,
            RecordType::SOA,
            RecordType::RRSIG,
            RecordType::NSEC,
        ]);

        NSECProofValidator::validateNxdomain('beta.example.com', [$nsec1, $nsec2]);

        static::assertTrue(true);
    }

    public function testNsecNodataProof(): void
    {
        $nsec = new NSECRecord('example.com', Duration::seconds(3600), 'mail.example.com', [
            RecordType::A,
            RecordType::SOA,
            RecordType::RRSIG,
            RecordType::NSEC,
        ]);

        NSECProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec]);

        static::assertTrue(true);
    }

    public function testNsecNodataThrowsWhenTypePresent(): void
    {
        $nsec = new NSECRecord(
            'example.com',
            Duration::seconds(3600),
            'mail.example.com',
            [RecordType::A, RecordType::AAAA],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('includes type AAAA');

        NSECProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec]);
    }

    public function testNsecNodataThrowsWhenNoMatchingRecord(): void
    {
        $nsec = new NSECRecord('other.example.com', Duration::seconds(3600), 'zzz.example.com', [RecordType::A]);

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('No NSEC record matches');

        NSECProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec]);
    }

    public function testNsec3NxdomainProof(): void
    {
        $salt = 'aabb';
        $iterations = 0;
        $algorithm = 1;

        $closestEncloser = 'example.com';
        $nextCloser = 'nonexistent.example.com';
        $wildcard = '*.example.com';

        $ceHash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute(
            $closestEncloser,
            $algorithm,
            $iterations,
            $salt,
        )));
        $ncHash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute($nextCloser, $algorithm, $iterations, $salt)));
        $wcHash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute($wildcard, $algorithm, $iterations, $salt)));

        $nsec3Ce = new NSEC3Record(
            $ceHash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            self::nextHash($ceHash),
            [RecordType::A, RecordType::SOA],
        );

        $nsec3Nc = self::coveringNsec3($ncHash, $algorithm, $iterations, $salt);
        $nsec3Wc = self::coveringNsec3($wcHash, $algorithm, $iterations, $salt);

        NSECProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce, $nsec3Nc, $nsec3Wc]);

        static::assertTrue(true);
    }

    public function testNsec3NodataProof(): void
    {
        $salt = '';
        $iterations = 0;
        $algorithm = 1;

        $hash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute('example.com', $algorithm, $iterations, $salt)));

        $nsec3 = new NSEC3Record(
            $hash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            self::nextHash($hash),
            [RecordType::A, RecordType::SOA],
        );

        NSECProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3]);

        static::assertTrue(true);
    }

    public function testNsec3NodataThrowsWhenTypePresent(): void
    {
        $salt = '';
        $iterations = 0;
        $algorithm = 1;

        $hash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute('example.com', $algorithm, $iterations, $salt)));

        $nsec3 = new NSEC3Record(
            $hash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            self::nextHash($hash),
            [RecordType::A, RecordType::AAAA],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('includes type AAAA');

        NSECProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3]);
    }

    public function testNsec3NxdomainMissingProofThrows(): void
    {
        $salt = '';
        $iterations = 0;
        $algorithm = 1;

        $ceHash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute('example.com', $algorithm, $iterations, $salt)));

        $nsec3Ce = new NSEC3Record(
            $ceHash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            self::nextHash($ceHash),
            [RecordType::A, RecordType::SOA],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('No NSEC3 record covers the next closer');

        NSECProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce]);
    }

    public function testNsecNxdomainWrapAround(): void
    {
        $nsecWrap = new NSECRecord(
            'zebra.example.com',
            Duration::seconds(3600),
            'alpha.example.com',
            [RecordType::A, RecordType::RRSIG, RecordType::NSEC],
        );

        $nsecWildcard = new NSECRecord(
            'example.com',
            Duration::seconds(3600),
            'alpha.example.com',
            [RecordType::SOA, RecordType::RRSIG, RecordType::NSEC],
        );

        NSECProofValidator::validateNxdomain('zzz.example.com', [$nsecWrap, $nsecWildcard]);

        static::assertTrue(true);
    }

    public function testNoNsecRecordsThrows(): void
    {
        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('No NSEC or NSEC3 records found');

        NSECProofValidator::validateNxdomain('example.com', []);
    }

    public function testNoNsecRecordsForNodataThrows(): void
    {
        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('No NSEC or NSEC3 records found');

        NSECProofValidator::validateNodata('example.com', RecordType::A, []);
    }

    private static function nextHash(string $hash): string
    {
        $bytes = Base32Hex::decode($hash);
        $len = Byte\length($bytes);
        /** @var non-negative-int $prefixLen */
        $prefixLen = $len - 1;
        $last = Byte\ord($bytes[$prefixLen]);
        $incremented = Byte\slice($bytes, 0, $prefixLen) . Byte\chr(($last + 1) % 256);

        return Base32Hex::encode($incremented);
    }

    private static function coveringNsec3(
        string $targetHash,
        int $algorithm,
        int $iterations,
        string $salt,
    ): NSEC3Record {
        $targetBytes = Base32Hex::decode($targetHash);
        $len = Byte\length($targetBytes);
        /** @var non-negative-int $prefixLen */
        $prefixLen = $len - 1;
        $last = Byte\ord($targetBytes[$prefixLen]);

        $beforeByte = ($last - 1 + 256) % 256;
        $afterByte = ($last + 1) % 256;

        $beforeBytes = Byte\slice($targetBytes, 0, $prefixLen) . Byte\chr($beforeByte);
        $afterBytes = Byte\slice($targetBytes, 0, $prefixLen) . Byte\chr($afterByte);

        $ownerHash = Byte\uppercase(Base32Hex::encode($beforeBytes));
        $nextHash = Byte\uppercase(Base32Hex::encode($afterBytes));

        return new NSEC3Record(
            $ownerHash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            $nextHash,
            [RecordType::RRSIG],
        );
    }
}
