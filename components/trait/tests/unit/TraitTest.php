<?php

declare(strict_types=1);

namespace Psl\Trait\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Trait;
use Psl\Trait\Tests\Fixture;

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
