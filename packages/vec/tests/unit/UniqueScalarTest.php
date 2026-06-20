<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class UniqueScalarTest extends TestCase
{
    public function testUniqueScalars(): void
    {
        $array = Vec\fill::<string>(10, 'foo');
        $array[] = 'bar';

        $unique = Vec\unique_scalar::<string>($array);

        static::assertCount(2, $unique);
        static::assertSame(['foo', 'bar'], $unique);
    }

    public function testUniqueIterator(): void
    {
        $array = Iter\Iterator::<int, string>::create(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Vec\unique_scalar::<string>($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], $unique);
    }

    public function testUniqueIteratorAgggregate(): void
    {
        $array = Collection\Map::<int, string>::fromArray(['foo', 'foo', 'bar', 'bar', 'baz']);

        $unique = Vec\unique_scalar::<string>($array);

        static::assertCount(3, $unique);
        static::assertSame(['foo', 'bar', 'baz'], $unique);
    }
}
