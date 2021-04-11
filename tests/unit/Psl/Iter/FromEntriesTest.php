<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class FromEntriesTest extends TestCase
{
    public function testEmptyEntries(): void
    {
        $actual = Iter\from_entries([]);
        static::assertCount(0, $actual);
    }

    public function testFromEntries(): void
    {
        $actual = Iter\from_entries([
            [1, 'hello'],
            [2, 'world']
        ]);

        $array = Iter\to_array_with_keys($actual);

        static::assertCount(2, $array);
        static::assertSame('hello', $array[1]);
        static::assertSame('world', $array[2]);
    }

    public function testFromEntriesWithNonArrayKeyKeys(): void
    {
        $actual = Iter\from_entries([
            [['1', '2'], 'hello'],
            [['3', '4'], 'world']
        ]);

        static::assertSame('hello', Iter\first($actual));
        static::assertSame('world', Iter\last($actual));

        static::assertSame(['1', '2'], Iter\first_key($actual));
        static::assertSame(['3', '4'], Iter\last_key($actual));
    }
}
