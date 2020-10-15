<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class JoinTest extends TestCase
{
    public function testJoin(): void
    {
        static::assertSame('abc', Str\join(['a', 'b', 'c'], ''));
        static::assertSame('Hello, World', Str\join(['Hello', 'World'], ', '));
        static::assertSame('foo / bar / baz', Str\join(['foo', 'bar', 'baz'], ' / '));
    }
}
