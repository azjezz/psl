<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class UniqueTest extends TestCase
{
    public function testUnique(): void
    {
        $array = Vec\fill::<string>(10, 'foo');

        $unique = Vec\unique::<string>($array);

        static::assertCount(1, $unique);

        static::assertSame('foo', Iter\first::<string>($unique));
    }

    public function testUniqueWithObjects(): void
    {
        $array = Vec\fill::<string>(10, 'foo');
        $object = new Collection\Map::<string, mixed>([]);
        $array = Vec\concat::<string|Collection\Map>($array, Vec\fill::<Collection\Map>(10, $object));

        $unique = Vec\unique::<string|Collection\Map>($array);

        static::assertCount(2, $unique);

        static::assertSame('foo', Iter\first::<string|Collection\Map>($unique));
        static::assertSame($object, Iter\last::<string|Collection\Map>($unique));
    }
}
