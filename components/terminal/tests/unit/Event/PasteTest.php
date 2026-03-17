<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Event\Paste;

final class PasteTest extends TestCase
{
    public function testConstruction(): void
    {
        $paste = new Paste('Hello, World!');

        static::assertSame('Hello, World!', $paste->text);
    }

    public function testEmptyText(): void
    {
        $paste = new Paste('');

        static::assertSame('', $paste->text);
    }

    public function testMultilineText(): void
    {
        $paste = new Paste("line1\nline2\nline3");

        static::assertSame("line1\nline2\nline3", $paste->text);
    }
}
