<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

class FilterNullsTest extends TestCase
{
    public function testFilterNulls(): void
    {
        self::assertCount(0, Iter\filter_nulls([]));
        self::assertCount(0, Iter\filter_nulls([null, null]));
        self::assertCount(1, Iter\filter_nulls([null, false]));
        self::assertCount(1, Iter\filter_nulls([null, 'null']));
        self::assertCount(1, Iter\filter_nulls(['null']));
        self::assertCount(1, Iter\filter_nulls(new Iter\Iterator(['null'])));
        self::assertCount(0, Iter\filter_nulls(new Iter\Iterator([null])));
        self::assertCount(0, Iter\filter_nulls(new Iter\Iterator([null, null])));
        self::assertCount(3, Iter\filter_nulls(new Iter\Iterator([null, false, '', 0])));
        self::assertCount(3, Iter\filter_nulls(new Collection\Vector([null, false, '', 0])));
        self::assertCount(3, Iter\filter_nulls(new Collection\Map([null, false, '', 0])));
        self::assertCount(3, Iter\filter_nulls((static function (): iterable {
            yield null;
            yield false;
            yield '';
            yield 0;
            yield null;
        })()));
    }
}
