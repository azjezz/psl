<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class FromKeysTest extends TestCase
{
    public function testFromKeys(): void
    {
        $actual = Dict\from_keys(['hello', 'world'], static fn(string $_key): bool => false);

        static::assertSame('hello', Iter\first_key($actual));
        static::assertSame('world', Iter\last_key($actual));
        static::assertFalse(Iter\first($actual));
        static::assertFalse(Iter\last($actual));
    }
}
