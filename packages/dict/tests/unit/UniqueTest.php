<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

final class UniqueTest extends TestCase
{
    public function testUnique(): void
    {
        $array = Vec\fill::<string>(10, 'foo');

        $unique = Dict\unique::<int, string>($array);

        static::assertCount(1, $unique);

        static::assertSame('foo', Iter\first::<string>($unique));
    }

    public function testUniqueWithObjects(): void
    {
        $array = Vec\fill::<string>(10, 'foo');
        $object = new Collection\Map::<int, mixed>([]);
        $array = Vec\concat::<string|Collection\Map<int, mixed>>($array, Vec\fill::<Collection\Map<int, mixed>>(10, $object));

        $unique = Dict\unique::<int, string|Collection\Map<int, mixed>>($array);

        static::assertCount(2, $unique);

        static::assertSame('foo', Iter\first::<string|Collection\Map<int, mixed>>($unique));
        static::assertSame($object, Iter\last::<string|Collection\Map<int, mixed>>($unique));
    }
}
