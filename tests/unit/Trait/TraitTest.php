<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Trait;

use PHPUnit\Framework\TestCase;
use Psl\Tests\Fixture;
use Psl\Trait;

final class TraitTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function test(string $trait_name, bool $defined, bool $exists): void
    {
        static::assertSame($defined, Trait\defined($trait_name));
        static::assertSame($exists, Trait\exists($trait_name));

        if ($exists) {
            static::assertTrue(Trait\defined($trait_name));
        }
    }

    public function provideData(): iterable
    {
        yield [Fixture\ExampleTrait::class, false, true];
        yield ['Psl\\Not\\Trait', false, false];
    }
}
