<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class AnyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAny(bool $expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\any($iterable, $predicate));
    }

    public function provideData(): iterable
    {
        yield [true, [false, true, true], static fn(bool $value): bool => $value];
        yield [true, [false, true, true], static fn(bool $value): bool => !$value];
        yield [true, [true, true, true], static fn(bool $value): bool => $value];
        yield [false, [true, true, true], static fn(bool $value): bool => !$value];
        yield [false, [false, false, false], static fn(bool $value): bool => $value];
        yield [true, [false, false, false], static fn(bool $value): bool => !$value];
        yield [true, [false, false, false], static fn(bool $_value): bool => true];
        yield [false, [false, false, false], static fn(bool $_value): bool => false];
        yield [false, [1, 2, 3], static fn(int $i): bool => $i > 3];
        yield [true, [4, 5, 6], static fn(int $i): bool => $i > 3];
        yield [true, [1, 2, 3, 4, 5, 6], static fn(int $i): bool => $i > 3];
        yield [false, [], static fn(bool $_value): bool => false];
    }
}
