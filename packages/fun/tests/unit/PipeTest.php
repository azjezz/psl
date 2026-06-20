<?php

declare(strict_types=1);

namespace Psl\Fun\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Str;

final class PipeTest extends TestCase
{
    public function testItCombinesMultipleFunctionToExecutesInOrder(): void
    {
        $x = Fun\pipe::<string>(
            static fn(string $x): string => $x . ' world',
            static fn(string $y): string => $y . '?',
            static fn(string $z): string => $z . '!',
        );

        static::assertSame('Hello world?!', $x('Hello'));
    }

    public function testItCombinesMultipleFunctionsThatDealWithDifferentTypes(): void
    {
        $x = Fun\pipe::<string>(Str\length(...), static fn(int $y): string => $y . '!');

        static::assertSame('5!', $x('Hello'));
    }

    public function testItCanCreateAnEmptyCombination(): void
    {
        $x = Fun\pipe::<string>();

        static::assertSame('Hello', $x('Hello'));
    }
}
