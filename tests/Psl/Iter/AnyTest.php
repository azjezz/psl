<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class AnyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAny(bool $expected, iterable $iterable, callable $predicate): void
    {
        self::assertSame($expected, Iter\any($iterable, $predicate));
    }

    public function provideData(): iterable
    {
        yield [true, [false, true, true], fn (bool $value): bool => $value];
        yield [true, [false, true, true], fn (bool $value): bool => !$value];
        yield [true, [true, true, true], fn (bool $value): bool => $value];
        yield [false, [true, true, true], fn (bool $value): bool => !$value];
        yield [false, [false, false, false], fn (bool $value): bool => $value];
        yield [true, [false, false, false], fn (bool $value): bool => !$value];
        yield [true, [false, false, false], fn (bool $value): bool => true];
        yield [false, [false, false, false], fn (bool $value): bool => false];
        yield [false, [1, 2, 3], fn (int $i): bool => $i > 3];
        yield [true, [4, 5, 6], fn (int $i): bool => $i > 3];
        yield [true, [1, 2, 3, 4, 5, 6], fn (int $i): bool => $i > 3];
        yield [false, [], fn (bool $value): bool => false];
    }
}
