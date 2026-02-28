<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\CIDR;

use PHPUnit\Framework\TestCase;
use Psl\CIDR\Block;
use Psl\CIDR\Exception\InvalidArgumentException;

final class BlockTest extends TestCase
{
    public function testIpv4ContainsWithinRange(): void
    {
        $block = new Block('192.168.1.0/24');

        static::assertTrue($block->contains('192.168.1.0'));
        static::assertTrue($block->contains('192.168.1.1'));
        static::assertTrue($block->contains('192.168.1.100'));
        static::assertTrue($block->contains('192.168.1.255'));
    }

    public function testIpv4DoesNotContainOutsideRange(): void
    {
        $block = new Block('192.168.1.0/24');

        static::assertFalse($block->contains('192.168.2.1'));
        static::assertFalse($block->contains('10.0.0.1'));
        static::assertFalse($block->contains('192.168.0.255'));
    }

    public function testIpv4LargeRange(): void
    {
        $block = new Block('10.0.0.0/8');

        static::assertTrue($block->contains('10.0.0.1'));
        static::assertTrue($block->contains('10.255.255.255'));
        static::assertFalse($block->contains('11.0.0.1'));
    }

    public function testIpv4SingleHost(): void
    {
        $block = new Block('192.168.1.1/32');

        static::assertTrue($block->contains('192.168.1.1'));
        static::assertFalse($block->contains('192.168.1.2'));
    }

    public function testIpv4ContainsAll(): void
    {
        $block = new Block('0.0.0.0/0');

        static::assertTrue($block->contains('0.0.0.0'));
        static::assertTrue($block->contains('255.255.255.255'));
        static::assertTrue($block->contains('192.168.1.1'));
    }

    public function testIpv6SingleHost(): void
    {
        $block = new Block('::1/128');

        static::assertTrue($block->contains('::1'));
        static::assertFalse($block->contains('::2'));
    }

    public function testIpv6Range(): void
    {
        $block = new Block('2001:db8::/32');

        static::assertTrue($block->contains('2001:db8::1'));
        static::assertTrue($block->contains('2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'));
        static::assertFalse($block->contains('2001:db9::1'));
    }

    public function testIpv4MappedIpv6(): void
    {
        $block = new Block('::ffff:1.2.3.4/128');

        static::assertTrue($block->contains('1.2.3.4'));
        static::assertTrue($block->contains('::ffff:1.2.3.4'));
    }

    public function testGitHubCidr(): void
    {
        $block = new Block('192.30.252.0/22');

        static::assertTrue($block->contains('192.30.252.0'));
        static::assertTrue($block->contains('192.30.253.1'));
        static::assertTrue($block->contains('192.30.255.255'));
        static::assertFalse($block->contains('192.30.251.255'));
        static::assertFalse($block->contains('192.31.0.0'));
    }

    public function testInvalidCidrNotation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Block('192.168.1.0');
    }

    public function testInvalidIpInCidr(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Block('not-an-ip/24');
    }

    public function testInvalidIpReturnsFalse(): void
    {
        $block = new Block('192.168.1.0/24');

        static::assertFalse($block->contains('not-an-ip'));
    }
}
