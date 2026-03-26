<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNSSEC\Exception\InvalidProofException;
use Psl\DNSSEC\Internal\NSEC\NSEC3Hash;

final class Nsec3HashAdditionalTest extends TestCase
{
    public function testComputeReturnsEmptyStringForUnsupportedAlgorithm(): void
    {
        $result = NSEC3Hash::compute('example.com', 99, 0, '');

        static::assertSame('', $result);
    }

    public function testComputeThrowsForExcessiveIterations(): void
    {
        $this->expectException(InvalidProofException::class);
        $this->expectExceptionMessage('iteration count 101 exceeds maximum');

        NSEC3Hash::compute('example.com', 1, 101, '');
    }

    public function testComputeWithIterationsProducesDifferentResult(): void
    {
        $hash0 = NSEC3Hash::compute('example.com', 1, 0, '');
        $hash5 = NSEC3Hash::compute('example.com', 1, 5, '');

        static::assertNotSame($hash0, $hash5);
        static::assertNotEmpty($hash0);
        static::assertNotEmpty($hash5);
    }

    public function testComputeWithSaltProducesDifferentResult(): void
    {
        $hashNoSalt = NSEC3Hash::compute('example.com', 1, 0, '');
        $hashWithSalt = NSEC3Hash::compute('example.com', 1, 0, 'aabb');

        static::assertNotSame($hashNoSalt, $hashWithSalt);
    }

    public function testComputeIsDeterministic(): void
    {
        $hash1 = NSEC3Hash::compute('example.com', 1, 0, 'aabb');
        $hash2 = NSEC3Hash::compute('example.com', 1, 0, 'aabb');

        static::assertSame($hash1, $hash2);
    }

    public function testComputeExactlyAt100Iterations(): void
    {
        $hash = NSEC3Hash::compute('example.com', 1, 100, '');

        static::assertNotEmpty($hash);
    }

    public function testComputeDifferentNamesProduceDifferentHashes(): void
    {
        $hash1 = NSEC3Hash::compute('foo.example.com', 1, 0, '');
        $hash2 = NSEC3Hash::compute('bar.example.com', 1, 0, '');

        static::assertNotSame($hash1, $hash2);
    }
}
