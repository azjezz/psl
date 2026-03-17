<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\OperatingSystemCommandKind;

final class OperatingSystemCommandKindTest extends TestCase
{
    public function testWindowIconAndTitle(): void
    {
        static::assertSame(0, OperatingSystemCommandKind::WindowIconAndTitle->value);
    }

    public function testWindowIcon(): void
    {
        static::assertSame(1, OperatingSystemCommandKind::WindowIcon->value);
    }

    public function testWindowTitle(): void
    {
        static::assertSame(2, OperatingSystemCommandKind::WindowTitle->value);
    }

    public function testChangeDirectory(): void
    {
        static::assertSame(7, OperatingSystemCommandKind::ChangeDirectory->value);
    }

    public function testHyperlink(): void
    {
        static::assertSame(8, OperatingSystemCommandKind::Hyperlink->value);
    }

    public function testNotify(): void
    {
        static::assertSame(9, OperatingSystemCommandKind::Notify->value);
    }

    public function testClipboard(): void
    {
        static::assertSame(52, OperatingSystemCommandKind::Clipboard->value);
    }
}
