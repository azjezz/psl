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

final class Nsec3OptOutTest extends TestCase
{
    public function testOptOutAcceptsWithoutNsInBitmap(): void
    {
        $salt = 'aabb';
        $iterations = 0;
        $algorithm = 1;

        $closestEncloser = 'example.com';
        $nextCloser = 'unsigned.example.com';

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

        $nsec3Nc = self::coveringNsec3WithFlags($ncHash, $algorithm, $iterations, $salt, 1, [RecordType::RRSIG]);

        NSEC3ProofValidator::validateNxdomain('unsigned.example.com', [$nsec3Ce, $nsec3Nc]);

        static::assertTrue(true);
    }

    public function testOptOutAcceptsWithNsAndDsInBitmap(): void
    {
        $salt = 'aabb';
        $iterations = 0;
        $algorithm = 1;

        $closestEncloser = 'example.com';
        $nextCloser = 'signed.example.com';

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

        $nsec3Nc = self::coveringNsec3WithFlags(
            $ncHash,
            $algorithm,
            $iterations,
            $salt,
            1,
            [RecordType::NS, RecordType::DS],
        );

        NSEC3ProofValidator::validateNxdomain('signed.example.com', [$nsec3Ce, $nsec3Nc]);

        static::assertTrue(true);
    }

    public function testOptOutAcceptsWithNsWithoutDs(): void
    {
        $salt = 'aabb';
        $iterations = 0;
        $algorithm = 1;

        $closestEncloser = 'example.com';
        $nextCloser = 'delegation.example.com';

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

        NSEC3ProofValidator::validateNxdomain('delegation.example.com', [$nsec3Ce, $nsec3Nc]);

        static::assertTrue(true);
    }

    public function testNoOptOutContinuesToWildcardCheck(): void
    {
        $salt = 'aabb';
        $iterations = 0;
        $algorithm = 1;

        $closestEncloser = 'example.com';
        $nextCloser = 'missing.example.com';

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

        $nsec3Nc = self::coveringNsec3WithFlags($ncHash, $algorithm, $iterations, $salt, 0, [RecordType::NS]);

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('wildcard');

        NSEC3ProofValidator::validateNxdomain('missing.example.com', [$nsec3Ce, $nsec3Nc]);
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
