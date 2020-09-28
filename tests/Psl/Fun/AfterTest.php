<?php

declare(strict_types=1);

namespace Psl\Tests\Fun;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Str;

class AfterTest extends TestCase
{
    public function testItCombinesAFunctionToExecuteAFunctionAfterAnotherFunction(): void
    {
        $x = Fun\after(
            fn (string $x): string => $x . ' world',
            fn (string $z): string => $z . '!!'
        );

        self::assertSame('Hello world!!', $x('Hello'));
    }

    public function testItCombinesAFunctionThatDealWithDifferentTypes(): void
    {
        $x = Fun\after(
            fn (string $x): int => Str\length($x),
            fn (int $z): string => $z . '!'
        );

        self::assertSame('5!', $x('Hello'));
    }
}
