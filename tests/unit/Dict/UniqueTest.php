<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

final class UniqueTest extends TestCase
{
    public function testUnique(): void
    {
        $array = Vec\fill(10, 'foo');

        $unique = Dict\unique($array);

        static::assertCount(1, $unique);

        static::assertSame('foo', Iter\first($unique));
    }

    public function testUniqueWithObjects(): void
    {
        $array = Vec\fill(10, 'foo');
        $object = new Collection\Map([]);
        $array = Vec\concat($array, Vec\fill(10, $object));

        $unique = Dict\unique($array);

        static::assertCount(2, $unique);

        static::assertSame('foo', Iter\first($unique));
        static::assertSame($object, Iter\last($unique));
    }
}
