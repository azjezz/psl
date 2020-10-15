<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class FilterNullsTest extends TestCase
{
    public function testFilterNulls(): void
    {
        static::assertCount(0, Iter\filter_nulls([]));
        static::assertCount(0, Iter\filter_nulls([null, null]));
        static::assertCount(1, Iter\filter_nulls([null, false]));
        static::assertCount(1, Iter\filter_nulls([null, 'null']));
        static::assertCount(1, Iter\filter_nulls(['null']));
        static::assertCount(1, Iter\filter_nulls(Iter\Iterator::create(['null'])));
        static::assertCount(0, Iter\filter_nulls(Iter\Iterator::create([null])));
        static::assertCount(0, Iter\filter_nulls(Iter\Iterator::create([null, null])));
        static::assertCount(3, Iter\filter_nulls(Iter\Iterator::create([null, false, '', 0])));
        static::assertCount(3, Iter\filter_nulls(new Collection\Vector([null, false, '', 0])));
        static::assertCount(3, Iter\filter_nulls(new Collection\Map([null, false, '', 0])));
        static::assertCount(3, Iter\filter_nulls((static function (): iterable {
            yield null;
            yield false;
            yield '';
            yield 0;
            yield null;
        })()));
    }
}
