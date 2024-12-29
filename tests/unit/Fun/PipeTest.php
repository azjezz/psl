<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Fun;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Str;

final class PipeTest extends TestCase
{
    public function testItCombinesMultipleFunctionToExecutesInOrder(): void
    {
        $x = Fun\pipe(
            static fn(string $x): string => $x . ' world',
            static fn(string $y): string => $y . '?',
            static fn(string $z): string => $z . '!',
        );

        static::assertSame('Hello world?!', $x('Hello'));
    }

    public function testItCombinesMultipleFunctionsThatDealWithDifferentTypes(): void
    {
        $x = Fun\pipe(static fn(string $x): int => Str\length($x), static fn(int $y): string => $y . '!');

        static::assertSame('5!', $x('Hello'));
    }

    public function testItCanCreateAnEmptyCombination(): void
    {
        $x = Fun\pipe();

        static::assertSame('Hello', $x('Hello'));
    }
}
