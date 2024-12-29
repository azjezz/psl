<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class AllTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAll(bool $expected, iterable $iterable, callable $predicate): void
    {
        static::assertSame($expected, Iter\all($iterable, $predicate));
    }

    public function provideData(): iterable
    {
        yield [false, [false, true, true], static fn(bool $value): bool => $value];
        yield [false, [false, true, true], static fn(bool $value): bool => !$value];
        yield [true, [true, true, true], static fn(bool $value): bool => $value];
        yield [false, [true, true, true], static fn(bool $value): bool => !$value];
        yield [false, [false, false, false], static fn(bool $value): bool => $value];
        yield [true, [false, false, false], static fn(bool $value): bool => !$value];
        yield [true, [false, false, false], static fn(bool $_value): bool => true];
        yield [false, [false, false, false], static fn(bool $_value): bool => false];
        yield [false, [1, 2, 3], static fn(int $i): bool => $i > 3];
        yield [true, [4, 5, 6], static fn(int $i): bool => $i > 3];
        yield [false, [1, 2, 3, 4, 5, 6], static fn(int $i): bool => $i > 3];
        yield [true, [], static fn(bool $_value): bool => false];
    }
}
