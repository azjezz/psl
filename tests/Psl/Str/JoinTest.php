<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class JoinTest extends TestCase
{
    public function testJoin(): void
    {
        self::assertSame('abc', Str\join(['a', 'b', 'c'], ''));
        self::assertSame('Hello, World', Str\join(['Hello', 'World'], ', '));
        self::assertSame('foo / bar / baz', Str\join(['foo', 'bar', 'baz'], ' / '));
    }
}
