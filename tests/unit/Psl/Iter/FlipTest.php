<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class FlipTest extends TestCase
{
    public function testFlip(): void
    {
        $a = new Collection\MutableVector(['a', 'b']);
        $b = new Collection\MutableVector(['c', 'd']);

        $iterable = Iter\Iterator::create(['a' => $a, 'b' => $b]);

        static::assertSame('a', Iter\first_key($iterable));
        static::assertSame($a, Iter\first($iterable));
        static::assertSame('b', Iter\last_key($iterable));
        static::assertSame($b, Iter\last($iterable));

        $result = Iter\flip($iterable);

        static::assertSame($a, Iter\first_key($result));
        static::assertSame('a', Iter\first($result));
        static::assertSame($b, Iter\last_key($result));
        static::assertSame('b', Iter\last($result));
    }
}
