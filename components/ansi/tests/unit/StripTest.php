<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;

final class StripTest extends TestCase
{
    public function testStripsSgrCodes(): void
    {
        $styled = "\e[1;31mhello\e[0m";

        static::assertSame('hello', Ansi\strip($styled));
    }

    public function testStripsCursorMovement(): void
    {
        $text = "\e[5Ahello";

        static::assertSame('hello', Ansi\strip($text));
    }

    public function testPlainTextUnchanged(): void
    {
        static::assertSame('hello world', Ansi\strip('hello world'));
    }

    public function testStripsMultipleSequences(): void
    {
        $text = "\e[1m\e[31mhello\e[0m \e[4mworld\e[0m";

        static::assertSame('hello world', Ansi\strip($text));
    }

    public function testStripsOscWithSt(): void
    {
        $text = "\e]2;My Title\e\\hello";

        static::assertSame('hello', Ansi\strip($text));
    }

    public function testStripsOscWithBel(): void
    {
        $text = "\e]2;My Title\x07hello";

        static::assertSame('hello', Ansi\strip($text));
    }

    public function testStripsHyperlink(): void
    {
        $text = "\e]8;;https://example.com\e\\click here\e]8;;\e\\";

        static::assertSame('click here', Ansi\strip($text));
    }

    public function testStripsMixedCsiAndOsc(): void
    {
        $text = "\e[1m\e]8;;https://example.com\e\\click here\e]8;;\e\\\e[0m";

        static::assertSame('click here', Ansi\strip($text));
    }
}
