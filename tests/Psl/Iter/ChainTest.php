<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class ChainTest extends TestCase
{
    public function testChain(): void
    {
        static::assertCount(0, Iter\chain());
        static::assertCount(0, Iter\chain([], Iter\Iterator::create([])));
        static::assertCount(0, Iter\chain([], Iter\Iterator::create([])));

        /** @var Iter\Iterator<int, int> $chain */
        $chain = Iter\chain(
            [1],
            Iter\range(2, 5),
            new Collection\Vector([6])
        );

        static::assertSame([1, 2, 3, 4, 5, 6], Iter\to_array($chain));
    }
}
