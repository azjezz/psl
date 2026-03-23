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

final class Nsec3ParameterConsistencyTest extends TestCase
{
    public function testConsistentParametersSucceeds(): void
    {
        $salt = 'aabb';
        $iterations = 0;
        $algorithm = 1;

        $hash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute('example.com', $algorithm, $iterations, $salt)));

        $nsec3a = new NSEC3Record(
            $hash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            self::nextHash($hash),
            [RecordType::A, RecordType::SOA],
        );

        $nsec3b = new NSEC3Record(
            'AAAAA.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt,
            'BBBBB',
            [RecordType::A],
        );

        NSEC3ProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3a, $nsec3b]);

        static::assertTrue(true);
    }

    public function testDifferentSaltThrows(): void
    {
        $algorithm = 1;
        $iterations = 0;
        $salt1 = 'aabb';
        $salt2 = 'ccdd';

        $hash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute('example.com', $algorithm, $iterations, $salt1)));

        $nsec3a = new NSEC3Record(
            $hash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt1,
            self::nextHash($hash),
            [RecordType::A, RecordType::SOA],
        );

        $nsec3b = new NSEC3Record(
            'AAAAA.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt2,
            'BBBBB',
            [RecordType::A],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('NSEC3 parameters are inconsistent across records.');

        NSEC3ProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3a, $nsec3b]);
    }

    public function testDifferentIterationsThrows(): void
    {
        $algorithm = 1;
        $salt = 'aabb';

        $hash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute('example.com', $algorithm, 0, $salt)));

        $nsec3a = new NSEC3Record(
            $hash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            0,
            $salt,
            self::nextHash($hash),
            [RecordType::A, RecordType::SOA],
        );

        $nsec3b = new NSEC3Record(
            'AAAAA.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            5,
            $salt,
            'BBBBB',
            [RecordType::A],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('NSEC3 parameters are inconsistent across records.');

        NSEC3ProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3a, $nsec3b]);
    }

    public function testDifferentAlgorithmsThrows(): void
    {
        $salt = 'aabb';
        $iterations = 0;

        $hash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute('example.com', 1, $iterations, $salt)));

        $nsec3a = new NSEC3Record(
            $hash . '.example.com',
            Duration::seconds(3600),
            1,
            0,
            $iterations,
            $salt,
            self::nextHash($hash),
            [RecordType::A, RecordType::SOA],
        );

        $nsec3b = new NSEC3Record(
            'AAAAA.example.com',
            Duration::seconds(3600),
            2,
            0,
            $iterations,
            $salt,
            'BBBBB',
            [RecordType::A],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('NSEC3 parameters are inconsistent across records.');

        NSEC3ProofValidator::validateNodata('example.com', RecordType::AAAA, [$nsec3a, $nsec3b]);
    }

    public function testSingleRecordSkipsConsistencyCheck(): void
    {
        $salt = 'aabb';
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

    public function testNxdomainDifferentSaltThrows(): void
    {
        $algorithm = 1;
        $iterations = 0;
        $salt1 = 'aabb';
        $salt2 = 'ccdd';

        $closestEncloser = 'example.com';
        $nextCloser = 'nonexistent.example.com';
        $wildcard = '*.example.com';

        $ceHash = Byte\uppercase(Base32Hex::encode(NSEC3Hash::compute(
            $closestEncloser,
            $algorithm,
            $iterations,
            $salt1,
        )));

        $nsec3Ce = new NSEC3Record(
            $ceHash . '.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt1,
            self::nextHash($ceHash),
            [RecordType::A, RecordType::SOA],
        );

        $nsec3Other = new NSEC3Record(
            'AAAAA.example.com',
            Duration::seconds(3600),
            $algorithm,
            0,
            $iterations,
            $salt2,
            'ZZZZZ',
            [RecordType::A],
        );

        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('NSEC3 parameters are inconsistent across records.');

        NSEC3ProofValidator::validateNxdomain('nonexistent.example.com', [$nsec3Ce, $nsec3Other]);
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
}
