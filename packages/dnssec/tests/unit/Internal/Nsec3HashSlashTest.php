<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNSSEC\Exception\InvalidProofException;
use Psl\DNSSEC\Internal\NSEC\NSEC3Hash;

final class Nsec3HashSlashTest extends TestCase
{
    public function testRejectsExcessiveIterationCount(): void
    {
        $this->expectException(InvalidProofException::class);

        NSEC3Hash::compute('example.com', 1, 500, '');
    }

    public function testRejectsVeryHighIterationCount(): void
    {
        $this->expectException(InvalidProofException::class);

        NSEC3Hash::compute('example.com', 1, 10_000, '');
    }

    public function testAcceptsZeroIterations(): void
    {
        $result = NSEC3Hash::compute('example.com', 1, 0, '');

        static::assertNotSame('', $result);
    }

    public function testAcceptsReasonableIterationCount(): void
    {
        $result = NSEC3Hash::compute('example.com', 1, 50, '');

        static::assertNotSame('', $result);
    }
}
