<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

final class UniqueScalarTest extends TestCase
{
    public function testUniqueScalars(): void
    {
        $array = Vec\fill(10, 'foo');
        $array[] = 'bar';

        $unique = Dict\unique_scalar($array);

        static::assertCount(2, $unique);
        static::assertSame(['foo', 'bar'], Vec\values($unique));
    }

    public function testUniqueIterator()
    {
        $array = Iter\Iterator::create(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Dict\unique_scalar($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], Vec\values($unique));
    }

    public function testUniqueIteratorAgggregate()
    {
        $array = Collection\Map::fromArray(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Dict\unique_scalar($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], Vec\values($unique));
    }
}
