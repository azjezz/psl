<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class FilterNullsTest extends TestCase
{
    public function testFilterNulls(): void
    {
        static::assertCount(0, Arr\filter_nulls([]));
        static::assertCount(0, Arr\filter_nulls([null, null]));
        static::assertCount(1, Arr\filter_nulls([null, false]));
        static::assertCount(1, Arr\filter_nulls([null, 'null']));
        static::assertCount(1, Arr\filter_nulls(['null']));
    }
}
