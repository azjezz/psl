<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Fun;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Str;

final class AfterTest extends TestCase
{
    public function testItCombinesAFunctionToExecuteAFunctionAfterAnotherFunction(): void
    {
        $x = Fun\after(static fn(string $x): string => $x . ' world', static fn(string $z): string => $z . '!!');

        static::assertSame('Hello world!!', $x('Hello'));
    }

    public function testItCombinesAFunctionThatDealWithDifferentTypes(): void
    {
        $x = Fun\after(static fn(string $x): int => Str\length($x), static fn(int $z): string => $z . '!');

        static::assertSame('5!', $x('Hello'));
    }
}
