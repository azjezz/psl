<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Internal\Base32Hex;
use Psl\DNS\Record\NSEC3Record;
use Psl\DNS\Record\RecordType;
use Psl\DNSSEC\Exception\InvalidProofException;
use Psl\DNSSEC\Internal\NSEC\NSEC3Hash;
use Psl\DNSSEC\Internal\NSEC\NSEC3ProofValidator;
use Psl\Str\Byte;

final class Nsec3ProofValidatorTest extends TestCase
{
    public function testValidateNxdomainSuccess(): void
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

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce, $nsec3Nc, $nsec3Wc]);

        static::assertTrue(true);
    }

    public function testValidateNxdomainMissingClosestEncloserThrows(): void
    {
        $nsec3 = new NSEC3Record('AAAAA.example.com', Duration::seconds(3600), 1, 0, 0, '', 'BBBBB', [RecordType::A]);

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('No closest encloser found');

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3]);
    }

    public function testValidateNxdomainMissingNextCloserCoverThrows(): void
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

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce]);
    }

    public function testValidateNxdomainOptOutUnsignedDelegation(): void
    {
        $salt = 'aabb';
        $iterations = 0;
        $algorithm = 1;

        $closestEncloser = 'example.com';
        $nextCloser = 'nonexistent.example.com';

        $ceHash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute(
            $closestEncloser,
            $algorithm,
            $iterations,
            $salt,
        )));
        $ncHash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute($nextCloser, $algorithm, $iterations, $salt)));

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

        $nsec3Nc = self::coveringNsec3WithFlags($ncHash, $algorithm, $iterations, $salt, 1, [RecordType::NS]);

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce, $nsec3Nc]);

        static::assertTrue(true);
    }

    public function testValidateNodataSuccess(): void
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

        NSEC3ProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3]);

        static::assertTrue(true);
    }

    public function testValidateNodataThrowsWhenTypePresent(): void
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

        NSEC3ProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3]);
    }

    public function testValidateNodataThrowsWhenNoMatchingHash(): void
    {
        $nsec3 = new NSEC3Record('AAAAA.example.com', Duration::seconds(3600), 1, 0, 0, '', 'BBBBB', [RecordType::A]);

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('No NSEC3 record matches');

        NSEC3ProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3]);
    }

    public function testValidateNxdomainThrowsWhenQueryNameItselfMatchesNsec3(): void
    {
        $salt = '';
        $iterations = 0;
        $algorithm = 1;

        $queryName = 'nonexistent.example.com';
        $hash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute($queryName, $algorithm, $iterations, $salt)));

        $nsec3 = new NSEC3Record(
            $hash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            self::nextHash($hash),
            [RecordType::A],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('No closest encloser found');

        NSEC3ProofValidator::validateNxdomain($queryName, [$nsec3]);
    }

    public function testValidateNxdomainUnsupportedHashAlgorithmThrows(): void
    {
        $nsec3 = new NSEC3Record('AAAAA.example.com', Duration::seconds(3600), 99, 0, 0, '', 'BBBBB', [RecordType::A]);

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('Unsupported NSEC3 hash algorithm');

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3]);
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
        return self::coveringNsec3WithFlags($targetHash, $algorithm, $iterations, $salt, 0, [RecordType::RRSIG]);
    }

    /**
     * @param list<RecordType> $types
     */
    private static function coveringNsec3WithFlags(
        string $targetHash,
        int $algorithm,
        int $iterations,
        string $salt,
        int $flags,
        array $types,
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
            $flags,
            $iterations,
            $salt,
            $nextHash,
            $types,
        );
    }
}
