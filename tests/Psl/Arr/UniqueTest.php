<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;

final class UniqueTest extends TestCase
{
    public function testUnique(): void
    {
        $array = Arr\fill('foo', 0, 10);

        $unique = Arr\unique($array);

        static::assertCount(1, $unique);

        static::assertSame('foo', Arr\firstx($unique));
    }

    public function testUniqueWithObjects(): void
    {
        $array  = Arr\fill('foo', 0, 10);
        $object = new Collection\Map([]);
        $array  = Arr\concat($array, Arr\fill($object, 0, 10));

        $unique = Arr\unique($array);

        static::assertCount(2, $unique);

        static::assertSame('foo', Arr\firstx($unique));
        static::assertSame($object, Arr\lastx($unique));
    }
}
