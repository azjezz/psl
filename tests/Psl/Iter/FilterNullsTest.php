<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
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
    }
}
