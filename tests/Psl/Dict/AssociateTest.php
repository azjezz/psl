<?php

declare(strict_types=1);

namespace Psl\Tests\Dict;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Collection;
use Psl\Dict;

final class AssociateTest extends TestCase
{
    public function testAssociate(): void
    {
        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Dict\associate(
            ['a', 'b', 'c'],
            [1, 2, 3]
        ));
    }

    public function testAssociateCollections(): void
    {
        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Dict\associate(
            Collection\Vector::fromArray(['a', 'b', 'c']),
            Collection\Vector::fromArray([1, 2, 3])
        ));
    }

    public function testAssociateWithMissingKeys(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected length of $keys and $values to be the same');

        Dict\associate(
            ['a', 'b', 'c'],
            [1, 2, 3, 4]
        );
    }

    public function testAssociateWithMissingValues(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected length of $keys and $values to be the same');

        Dict\associate(
            ['a', 'b', 'c', 'd', 'e', 'f'],
            [1, 2, 3, 4]
        );
    }
}
