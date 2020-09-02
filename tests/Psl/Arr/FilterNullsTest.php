<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class FilterNullsTest extends TestCase
{
    public function testFilterNulls(): void
    {
        self::assertCount(0, Arr\filter_nulls([]));
        self::assertCount(0, Arr\filter_nulls([null, null]));
        self::assertCount(1, Arr\filter_nulls([null, false]));
        self::assertCount(1, Arr\filter_nulls([null, 'null']));
        self::assertCount(1, Arr\filter_nulls(['null']));
    }
}
