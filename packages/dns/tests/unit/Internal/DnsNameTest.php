<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNS\Internal\DNSName;

final class DnsNameTest extends TestCase
{
    public function testCaselessEqualsMatchesSameCase(): void
    {
        static::assertTrue(DNSName::caselessEquals('example.com', 'example.com'));
    }

    public function testCaselessEqualsMatchesDifferentCase(): void
    {
        static::assertTrue(DNSName::caselessEquals('EXAMPLE.COM', 'example.com'));
    }

    public function testCaselessEqualsRejectsDifferentNames(): void
    {
        static::assertFalse(DNSName::caselessEquals('example.com', 'example.org'));
    }

    public function testCaselessEqualsEmptyStrings(): void
    {
        static::assertTrue(DNSName::caselessEquals('', ''));
    }

    public function testCanonicalOrderSameNameReturnsZero(): void
    {
        static::assertSame(0, DNSName::canonicalOrder('example.com', 'example.com'));
    }

    public function testCanonicalOrderCaseInsensitive(): void
    {
        static::assertSame(0, DNSName::canonicalOrder('EXAMPLE.COM', 'example.com'));
    }

    public function testCanonicalOrderSortsByRightmostLabel(): void
    {
        static::assertLessThan(0, DNSName::canonicalOrder('a.com', 'a.org'));
        static::assertGreaterThan(0, DNSName::canonicalOrder('a.org', 'a.com'));
    }

    public function testCanonicalOrderParentBeforeChild(): void
    {
        static::assertLessThan(0, DNSName::canonicalOrder('example.com', 'a.example.com'));
    }

    public function testCanonicalOrderSiblings(): void
    {
        static::assertLessThan(0, DNSName::canonicalOrder('a.example.com', 'b.example.com'));
        static::assertGreaterThan(0, DNSName::canonicalOrder('z.example.com', 'a.example.com'));
    }

    public function testGetAncestorsFromLeaf(): void
    {
        $ancestors = DNSName::getAncestors('sub.example.com');

        static::assertSame(['sub.example.com', 'example.com', 'com', '.'], $ancestors);
    }

    public function testGetAncestorsSingleLabel(): void
    {
        $ancestors = DNSName::getAncestors('com');

        static::assertSame(['com', '.'], $ancestors);
    }

    public function testGetAncestorsEmpty(): void
    {
        static::assertSame(['.'], DNSName::getAncestors(''));
        static::assertSame(['.'], DNSName::getAncestors('.'));
    }

    public function testGetParentName(): void
    {
        static::assertSame('example.com', DNSName::getParentName('sub.example.com'));
    }

    public function testGetParentNameTld(): void
    {
        static::assertSame('com', DNSName::getParentName('example.com'));
    }

    public function testGetParentNameSingleLabel(): void
    {
        static::assertSame('.', DNSName::getParentName('com'));
    }

    public function testGetParentNameEmpty(): void
    {
        static::assertSame('.', DNSName::getParentName(''));
    }
}
