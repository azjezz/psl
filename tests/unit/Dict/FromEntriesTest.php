<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class FromEntriesTest extends TestCase
{
    public function testEmptyEntries(): void
    {
        $actual = Dict\from_entries([]);
        static::assertCount(0, $actual);
    }

    public function testFromEntries(): void
    {
        $array = Dict\from_entries([
            [1, 'hello'],
            [2, 'world'],
        ]);

        static::assertCount(2, $array);
        static::assertSame('hello', $array[1]);
        static::assertSame('world', $array[2]);
    }
}
