<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class UniqueScalarTest extends TestCase
{
    public function testUniqueScalars(): void
    {
        $array = Vec\fill(10, 'foo');
        $array[] = 'bar';

        $unique = Vec\unique_scalar($array);

        static::assertCount(2, $unique);
        static::assertSame(['foo', 'bar'], $unique);
    }

    public function testUniqueIterator(): void
    {
        $array = Iter\Iterator::create(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Vec\unique_scalar($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], $unique);
    }

    public function testUniqueIteratorAgggregate(): void
    {
        $array = Collection\Map::fromArray(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Vec\unique_scalar($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], $unique);
    }
}
