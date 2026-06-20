<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class FromKeysTest extends TestCase
{
    public function testFromKeys(): void
    {
        $actual = Dict\from_keys::<string, bool>(['hello', 'world'], static fn(string $_): bool => false);

        static::assertSame('hello', Iter\first_key::<string, bool>($actual));
        static::assertSame('world', Iter\last_key::<string, bool>($actual));
        static::assertFalse(Iter\first::<bool>($actual));
        static::assertFalse(Iter\last::<bool>($actual));
    }
}
