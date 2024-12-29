<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Interface;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Interface;
use Psl\Type;

final class InterfaceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function test(string $interface_name, bool $exists): void
    {
        static::assertSame($exists, Interface\exists($interface_name));
        static::assertSame($exists, Interface\defined($interface_name));
    }

    public function provideData(): iterable
    {
        yield [Collection\VectorInterface::class, true];
        yield [Collection\MutableVectorInterface::class, true];
        yield [Collection\MapInterface::class, true];
        yield [Collection\MutableMapInterface::class, true];
        yield [Type\TypeInterface::class, true];

        yield [Collection\Vector::class, false];
        yield [Collection\MutableVector::class, false];
        yield [Collection\Map::class, false];
        yield [Collection\MutableMap::class, false];
        yield [Type\Type::class, false];

        yield ['Psl\\Not\\Interface', false];
    }
}
