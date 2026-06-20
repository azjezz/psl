<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class FilterNullsTest extends TestCase
{
    public function testFilterNulls(): void
    {
        static::assertCount(0, Vec\filter_nulls::<int|float|string|bool>([]));
        static::assertCount(0, Vec\filter_nulls::<int|float|string|bool>([null, null]));
        static::assertCount(1, Vec\filter_nulls::<bool>([null, false]));
        static::assertCount(1, Vec\filter_nulls::<string>([null, 'null']));
        static::assertCount(1, Vec\filter_nulls::<string>(['null']));
        static::assertCount(1, Vec\filter_nulls::<string>(Iter\Iterator::<int, string>::create(['null'])));
        static::assertCount(0, Vec\filter_nulls::<int|float|string|bool>(Iter\Iterator::<int, null>::create([null])));
        static::assertCount(0, Vec\filter_nulls::<int|float|string|bool>(Iter\Iterator::<int, null>::create([null, null])));
        static::assertCount(3, Vec\filter_nulls::<bool|string|int>(Iter\Iterator::<int, bool|string|int|null>::create([null, false, '', 0])));
        static::assertCount(3, Vec\filter_nulls::<bool|string|int>(new Collection\Vector::<bool|string|int|null>([null, false, '', 0])));
        static::assertCount(3, Vec\filter_nulls::<bool|string|int>(new Collection\Map::<int, bool|string|int|null>([null, false, '', 0])));
        static::assertCount(
            3,
            Vec\filter_nulls::<bool|string|int>(
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
