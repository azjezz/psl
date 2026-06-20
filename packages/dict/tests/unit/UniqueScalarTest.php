<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

final class UniqueScalarTest extends TestCase
{
    public function testUniqueScalars(): void
    {
        $array = Vec\fill::<string>(10, 'foo');
        $array[] = 'bar';

        $unique = Dict\unique_scalar::<int, string>($array);

        static::assertCount(2, $unique);
        static::assertSame(['foo', 'bar'], Vec\values::<string>($unique));
    }

    public function testUniqueIterator(): void
    {
        $array = Iter\Iterator::<int, string>::create(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Dict\unique_scalar::<int, string>($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], Vec\values::<string>($unique));
    }

    public function testUniqueIteratorAgggregate(): void
    {
        $array = Collection\Map::<int, string>::fromArray(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Dict\unique_scalar::<int, string>($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], Vec\values::<string>($unique));
    }
}
