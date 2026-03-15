<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Trait;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Tests\Fixture;
use Psl\Trait;

final class TraitTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(string $traitName, bool $defined, bool $exists): void
    {
        static::assertSame($defined, Trait\defined($traitName));
        static::assertSame($exists, Trait\exists($traitName));

        if ($exists) {
            static::assertTrue(Trait\defined($traitName));
        }
    }

    public static function provideData(): iterable
    {
        yield [Fixture\ExampleTrait::class, false, true];
        yield ['Psl\\Not\\Trait', false, false];
    }
}
