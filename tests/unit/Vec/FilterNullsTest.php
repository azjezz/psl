<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class FilterNullsTest extends TestCase
{
    public function testFilterNulls(): void
    {
        static::assertCount(0, Vec\filter_nulls([]));
        static::assertCount(0, Vec\filter_nulls([null, null]));
        static::assertCount(1, Vec\filter_nulls([null, false]));
        static::assertCount(1, Vec\filter_nulls([null, 'null']));
        static::assertCount(1, Vec\filter_nulls(['null']));
        static::assertCount(1, Vec\filter_nulls(Iter\Iterator::create(['null'])));
        static::assertCount(0, Vec\filter_nulls(Iter\Iterator::create([null])));
        static::assertCount(0, Vec\filter_nulls(Iter\Iterator::create([null, null])));
        static::assertCount(3, Vec\filter_nulls(Iter\Iterator::create([null, false, '', 0])));
        static::assertCount(3, Vec\filter_nulls(new Collection\Vector([null, false, '', 0])));
        static::assertCount(3, Vec\filter_nulls(new Collection\Map([null, false, '', 0])));
        static::assertCount(
            3,
            Vec\filter_nulls((static function (): iterable {
                yield null;
                yield false;
                yield '';
                yield 0;
                yield null;
            })()),
        );
    }
}
