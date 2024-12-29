<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Class;

use PHPUnit\Framework\TestCase;
use Psl\Class;
use Psl\Collection;
use Psl\Type;

final class ClassTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function test(
        string $classname,
        bool $exists,
        bool $final,
        bool $readonly,
        bool $abstract,
        array $methods = [],
        array $constants = [],
    ): void {
        static::assertSame($exists, Class\exists($classname));
        static::assertSame($exists, Class\defined($classname));
        if (!$exists) {
            return;
        }

        static::assertSame($final, Class\is_final($classname));
        static::assertSame($readonly, Class\is_readonly($classname));
        static::assertSame($abstract, Class\is_abstract($classname));

        foreach ($methods as $method) {
            static::assertTrue(CLass\has_method($classname, $method));
        }

        static::assertFalse(Class\has_method($classname, 'ImNotAClassMethod'));

        foreach ($constants as $constant) {
            static::assertTrue(Class\has_constant($classname, $constant));
        }

        static::assertFalse(Class\has_constant($classname, 'I_AM_NOT_A_CONSTANT'));
    }

    public function provideData(): iterable
    {
        yield [Collection\Vector::class, true, true, true, false, ['first', 'last'], []];
        yield [Collection\MutableVector::class, true, true, false, false, ['first', 'last'], []];
        yield [Collection\Map::class, true, true, true, false, ['first', 'last'], []];
        yield [Collection\MutableMap::class, true, true, false, false, ['first', 'last'], []];
        yield [Type\Type::class, true, false, true, true, ['matches', 'isOptional'], []];

        yield ['Psl\\Not\\Class', false, false, false, false, [], []];
    }
}
