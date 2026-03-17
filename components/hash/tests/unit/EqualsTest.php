<?php

declare(strict_types=1);

namespace Psl\Hash\Tests\Unit;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Hash;

final class EqualsTest extends TestCase
{
    /**
     * @param non-empty-string $knownString
     * @param non-empty-string $userString
     */
    #[DataProvider('provideEqualsData')]
    public function testEquals(bool $expected, string $knownString, string $userString): void
    {
        static::assertSame($expected, Hash\equals($knownString, $userString));
    }

    /**
     * @return Generator<int, array{0: bool, 1: non-empty-string, 2: non-empty-string}, mixed, void>
     */
    public static function provideEqualsData(): Generator
    {
        yield [true, 'hello', 'hello'];
        yield [false, 'hey', 'hello'];
        yield [false, 'hello', 'hey'];
    }
}
