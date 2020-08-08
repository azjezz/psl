<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class FromKeysTest extends TestCase
{
    public function testFromKeys(): void
    {
        $actual = Iter\from_keys(['hello', 'world'], fn (string $_key) => false);

        self::assertSame('hello', Iter\first_key($actual));
        self::assertSame('world', Iter\last_key($actual));
        self::assertFalse(Iter\first($actual));
        self::assertFalse(Iter\last($actual));
    }

    public function testFromKeysWithNonArrayKeyKeys(): void
    {
        $actual = Iter\from_keys([
            [1, 'hello'],
            [2, 'world']
        ], fn (array $k) => $k[1]);

        self::assertSame([1, 'hello'], Iter\first_key($actual));
        self::assertSame([2, 'world'], Iter\last_key($actual));
        self::assertSame('hello', Iter\first($actual));
        self::assertSame('world', Iter\last($actual));
    }
}
