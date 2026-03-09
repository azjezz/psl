<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Ansi;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;

final class ContainsTest extends TestCase
{
    public function testTrueForStyledText(): void
    {
        static::assertTrue(Ansi\contains("\e[1mhello\e[0m"));
    }

    public function testFalseForPlainText(): void
    {
        static::assertFalse(Ansi\contains('hello world'));
    }

    public function testDetectsSgrSequence(): void
    {
        static::assertTrue(Ansi\contains("\e[31mred\e[0m"));
    }

    public function testDetectsCursorSequence(): void
    {
        static::assertTrue(Ansi\contains("\e[5Ahello"));
    }

    public function testDetectsScreenSequence(): void
    {
        static::assertTrue(Ansi\contains("\e[2Jclear"));
    }

    public function testDetectsOsc(): void
    {
        static::assertTrue(Ansi\contains("\e]2;My Title\e\\"));
    }

    public function testDetectsOscWithBel(): void
    {
        static::assertTrue(Ansi\contains("\e]2;My Title\x07"));
    }

    public function testDetectsHyperlink(): void
    {
        static::assertTrue(Ansi\contains("\e]8;;https://example.com\e\\click\e]8;;\e\\"));
    }
}
