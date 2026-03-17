<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class FormatTest extends TestCase
{
    public function testFormatWithArgs(): void
    {
        static::assertSame('Hello, world', Str\format('Hello, %s', 'world'));
    }

    public function testFormatWithMultipleArgs(): void
    {
        static::assertSame('foo is 3 characters long', Str\format('%s is %d characters long', 'foo', 3));
    }

    public function testFormatWithNoArgsReturnsMessageAsIs(): void
    {
        static::assertSame('hello %s', Str\format('hello %s'));
    }

    public function testFormatWithNoArgsPreservesDoublePercent(): void
    {
        static::assertSame('100%%', Str\format('100%%'));
    }

    public function testFormatWithArgsResolvesDoublePercent(): void
    {
        static::assertSame('100% done', Str\format('100%% %s', 'done'));
    }
}
