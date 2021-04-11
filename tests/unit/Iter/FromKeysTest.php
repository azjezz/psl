<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class FromKeysTest extends TestCase
{
    public function testFromKeys(): void
    {
        $actual = Iter\from_keys(['hello', 'world'], static fn (string $_key) => false);

        static::assertSame('hello', Iter\first_key($actual));
        static::assertSame('world', Iter\last_key($actual));
        static::assertFalse(Iter\first($actual));
        static::assertFalse(Iter\last($actual));
    }

    public function testFromKeysWithNonArrayKeyKeys(): void
    {
        $actual = Iter\from_keys([
            [1, 'hello'],
            [2, 'world']
        ], static fn (array $k) => $k[1]);

        static::assertSame([1, 'hello'], Iter\first_key($actual));
        static::assertSame([2, 'world'], Iter\last_key($actual));
        static::assertSame('hello', Iter\first($actual));
        static::assertSame('world', Iter\last($actual));
    }
}
