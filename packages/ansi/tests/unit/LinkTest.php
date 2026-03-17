<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\Exception;
use Psl\Ansi\Style;

final class LinkTest extends TestCase
{
    public function testBasicLink(): void
    {
        $result = Ansi\link('click here', 'https://example.com');

        static::assertSame("\e]8;;https://example.com\e\\click here\e]8;;\e\\", $result);
    }

    public function testLinkWithStyles(): void
    {
        $result = Ansi\link('click here', 'https://example.com', Style\bold());

        static::assertSame("\e[1m\e]8;;https://example.com\e\\click here\e]8;;\e\\\e[0m", $result);
    }

    public function testLinkWithColor(): void
    {
        $result = Ansi\link('click here', 'https://example.com', Style\underline(), Ansi\foreground(Ansi\Color\blue()));

        static::assertSame("\e[4;34m\e]8;;https://example.com\e\\click here\e]8;;\e\\\e[0m", $result);
    }

    public function testNonSgrThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        Ansi\link('click here', 'https://example.com', Ansi\Cursor\up(1));
    }

    public function testEmptyUrl(): void
    {
        $result = Ansi\link('text', '');

        static::assertSame("\e]8;;\e\\text\e]8;;\e\\", $result);
    }
}
