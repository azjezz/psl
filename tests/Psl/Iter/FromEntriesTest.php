<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class FromEntriesTest extends TestCase
{
    public function testEmptyEntries(): void
    {
        $actual = Iter\from_entries([]);
        self::assertCount(0, $actual);
    }

    public function testFromEntries(): void
    {
        $actual = Iter\from_entries([
            [1, 'hello'],
            [2, 'world']
        ]);

        $array = Iter\to_array_with_keys($actual);

        self::assertCount(2, $array);
        self::assertSame('hello', $array[1]);
        self::assertSame('world', $array[2]);
    }

    public function testFromEntriesWithNonArrayKeyKeys(): void
    {
        $actual = Iter\from_entries([
            [['1', '2'], 'hello'],
            [['3', '4'], 'world']
        ]);

        self::assertSame('hello', Iter\first($actual));
        self::assertSame('world', Iter\last($actual));

        self::assertSame(['1', '2'], Iter\first_key($actual));
        self::assertSame(['3', '4'], Iter\last_key($actual));
    }
}
