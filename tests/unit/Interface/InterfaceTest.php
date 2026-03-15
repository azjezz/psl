<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Interface;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Interface;
use Psl\Type;

final class InterfaceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(string $interfaceName, bool $exists): void
    {
        static::assertSame($exists, Interface\exists($interfaceName));
        static::assertSame($exists, Interface\defined($interfaceName));
    }

    public static function provideData(): iterable
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
