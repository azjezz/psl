<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNS\Internal\Base32Hex;
use Psl\DNSSEC\Internal\NSEC\NSEC3Hash;
use Psl\Str\Byte;

final class Nsec3HashTest extends TestCase
{
    public function testComputeWithSalt(): void
    {
        $hash = NSEC3Hash::compute('example.com', 1, 0, 'aabb');

        static::assertNotSame('', $hash);
        static::assertSame(20, Byte\length($hash));
    }

    public function testComputeWithEmptySalt(): void
    {
        $hash = NSEC3Hash::compute('example.com', 1, 0, '');

        static::assertNotSame('', $hash);
        static::assertSame(20, Byte\length($hash));
    }

    public function testComputeWithIterations(): void
    {
        $hash0 = NSEC3Hash::compute('example.com', 1, 0, 'aabb');
        $hash5 = NSEC3Hash::compute('example.com', 1, 5, 'aabb');

        static::assertNotSame($hash0, $hash5);
    }

    public function testComputeUnsupportedAlgorithm(): void
    {
        $hash = NSEC3Hash::compute('example.com', 99, 0, '');

        static::assertSame('', $hash);
    }

    public function testComputeDeterministic(): void
    {
        $hash1 = NSEC3Hash::compute('example.com', 1, 2, 'dead');
        $hash2 = NSEC3Hash::compute('example.com', 1, 2, 'dead');

        static::assertSame($hash1, $hash2);
    }

    public function testComputeCaseInsensitive(): void
    {
        $hashLower = NSEC3Hash::compute('example.com', 1, 0, '');
        $hashUpper = NSEC3Hash::compute('EXAMPLE.COM', 1, 0, '');

        static::assertSame($hashLower, $hashUpper);
    }

    public function testComputeBase32HexEncoded(): void
    {
        $hash = NSEC3Hash::compute('example.com', 1, 0, '');
        $encoded = Base32Hex::encode($hash);

        static::assertMatchesRegularExpression('/^[0-9A-V]+$/', $encoded);
    }
}
