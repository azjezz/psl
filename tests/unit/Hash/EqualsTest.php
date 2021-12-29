<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Hash;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Hash;

final class EqualsTest extends TestCase
{
    /**
     * @param non-empty-string $known_string
     * @param non-empty-string $user_string
     *
     * @dataProvider provideEqualsData
     */
    public function testEquals(bool $expected, string $known_string, string $user_string): void
    {
        static::assertSame($expected, Hash\equals($known_string, $user_string));
    }

    /**
     * @return Generator<int, array{0: bool, 1: non-empty-string, 2: non-empty-string}, mixed, void>
     */
    public function provideEqualsData(): Generator
    {
        yield [true, 'hello', 'hello'];
        yield [false, 'hey', 'hello'];
        yield [false, 'hello', 'hey'];
    }
}
