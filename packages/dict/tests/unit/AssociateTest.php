<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class AssociateTest extends TestCase
{
    public function testAssociate(): void
    {
        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Dict\associate::<string, int>(['a', 'b', 'c'], [1, 2, 3]));
    }

    public function testAssociateEmpty(): void
    {
        static::assertSame([], Dict\associate::<string, int>([], []));
    }

    public function testAssociateCollections(): void
    {
        static::assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            Dict\associate::<string, int>(Collection\Vector::<string>::fromArray(['a', 'b', 'c']), Collection\Vector::<int>::fromArray([1, 2, 3])),
        );
    }

    public function testAssociateWithMissingKeys(): void
    {
        $this->expectException(Dict\Exception\LogicException::class);
        $this->expectExceptionMessage('Expected length of $keys and $values to be the same');

        Dict\associate::<string, int>(['a', 'b', 'c'], [1, 2, 3, 4]);
    }

    public function testAssociateWithMissingValues(): void
    {
        $this->expectException(Dict\Exception\LogicException::class);
        $this->expectExceptionMessage('Expected length of $keys and $values to be the same');

        Dict\associate::<string, int>(['a', 'b', 'c', 'd', 'e', 'f'], [1, 2, 3, 4]);
    }
}
