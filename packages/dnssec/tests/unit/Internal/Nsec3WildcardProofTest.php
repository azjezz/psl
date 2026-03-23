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

final class Nsec3WildcardProofTest extends TestCase
{
    public function testMatchingWildcardHashShouldThrow(): void
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

        $nsec3WcMatch = new NSEC3Record(
            $wcHash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            self::nextHash($wcHash),
            [RecordType::A],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('wildcard');

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce, $nsec3Nc, $nsec3WcMatch]);
    }

    public function testCoveringWildcardHashSucceeds(): void
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

    public function testNoCoveringWildcardThrows(): void
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

        $nsec3Nc = self::coveringNsec3($ncHash, $algorithm, $iterations, $salt);

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('wildcard');

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce, $nsec3Nc]);
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
