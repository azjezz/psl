<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class AssociateTest extends TestCase
{
    public function testAssociate(): void
    {
        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Dict\associate(['a', 'b', 'c'], [1, 2, 3]));
    }

    public function testAssociateEmpty(): void
    {
        static::assertSame([], Dict\associate([], []));
    }

    public function testAssociateCollections(): void
    {
        static::assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            Dict\associate(Collection\Vector::fromArray(['a', 'b', 'c']), Collection\Vector::fromArray([1, 2, 3])),
        );
    }

    public function testAssociateWithMissingKeys(): void
    {
        $this->expectException(Dict\Exception\LogicException::class);
        $this->expectExceptionMessage('Expected length of $keys and $values to be the same');

        Dict\associate(['a', 'b', 'c'], [1, 2, 3, 4]);
    }

    public function testAssociateWithMissingValues(): void
    {
        $this->expectException(Dict\Exception\LogicException::class);
        $this->expectExceptionMessage('Expected length of $keys and $values to be the same');

        Dict\associate(['a', 'b', 'c', 'd', 'e', 'f'], [1, 2, 3, 4]);
    }
}
