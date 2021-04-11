<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class FlattenTest extends TestCase
{
    public function testFlatten(): void
    {
        $result = Iter\flatten([
            [1, 2, 3],
            new Collection\Map(['a' => 'b', 'c' => 'd', 'e' => 'f']),
            (static fn () => yield 'hey' => 'hello')()
        ]);

        static::assertSame([
            0 => 1,
            1 => 2,
            2 => 3,
            'a' => 'b',
            'c' => 'd',
            'e' => 'f',
            'hey' => 'hello'
        ], Iter\to_array_with_keys($result));
    }

    public function testFlattenSameKeys(): void
    {
        $result = Iter\flatten(new Collection\Vector([
            new Collection\MutableMap(['a' => 'b']),
            ['a' => 'foo']
        ]));

        static::assertSame('a', Iter\first_key($result));
        static::assertSame('b', Iter\first($result));

        static::assertSame('a', Iter\last_key($result));
        static::assertSame('foo', Iter\last($result));
    }
}
