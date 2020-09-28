<?php

declare(strict_types=1);

namespace Psl\Tests\Fun;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Str\Byte;

class PipeTest extends TestCase
{
    public function testItCombinesMultipleFunctionToExecutesInOrder(): void
    {
        $x = Fun\pipe(
            fn (string $x): string => $x . ' world',
            fn (string $y): string => $y . '?',
            fn (string $z): string => $z . '!',
        );

        self::assertSame('Hello world?!', $x('Hello'));
    }

    public function testItCombinesMultipleFunctionsThatDealWithDifferentTypes(): void
    {
        $x = Fun\pipe(
            fn (string $x): int => Byte\length($x),
            fn (int $y): string => $y . '!'
        );

        self::assertSame('5!', $x('Hello'));
    }

    /** @test */
    public function testItCanCreateAnEmptyCombination(): void
    {
        $x = Fun\pipe();

        self::assertSame('Hello', $x('Hello'));
    }
}
