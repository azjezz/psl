<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Iter;

final class FilterNullsTest extends TestCase
{
    public function testFilterNulls(): void
    {
        static::assertCount(0, Dict\filter_nulls::<int, mixed>([]));
        static::assertCount(0, Dict\filter_nulls::<int, mixed>([null, null]));
        static::assertCount(1, Dict\filter_nulls::<int, mixed>([null, false]));
        static::assertCount(1, Dict\filter_nulls::<int, mixed>([null, 'null']));
        static::assertCount(1, Dict\filter_nulls::<int, mixed>(['null']));
        static::assertCount(1, Dict\filter_nulls::<int, mixed>(Iter\Iterator::<int, mixed>::create(['null'])));
        static::assertCount(0, Dict\filter_nulls::<int, mixed>(Iter\Iterator::<int, mixed>::create([null])));
        static::assertCount(0, Dict\filter_nulls::<int, mixed>(Iter\Iterator::<int, mixed>::create([null, null])));
        static::assertCount(3, Dict\filter_nulls::<int, mixed>(Iter\Iterator::<int, mixed>::create([null, false, '', 0])));
        static::assertCount(3, Dict\filter_nulls::<int, mixed>(new Collection\Vector::<mixed>([null, false, '', 0])));
        static::assertCount(3, Dict\filter_nulls::<int, mixed>(new Collection\Map::<int, mixed>([null, false, '', 0])));
        static::assertCount(
            3,
            Dict\filter_nulls::<int, mixed>(
                (static function (): iterable {
                    yield null;
                    yield false;
                    yield '';
                    yield 0;
                    yield null;
                })(),
            ),
        );
    }
}
