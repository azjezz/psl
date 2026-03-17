<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit\Cursor;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\ControlSequenceIntroducerKind;
use Psl\Ansi\Cursor;

final class CursorTest extends TestCase
{
    public function testUp(): void
    {
        $sequence = Cursor\up(5);

        static::assertSame('5', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::CursorUp, $sequence->kind);
        static::assertSame("\e[5A", $sequence->toString());
    }

    public function testUpDefault(): void
    {
        $sequence = Cursor\up();

        static::assertSame('1', $sequence->parameters);
    }

    public function testDown(): void
    {
        $sequence = Cursor\down(3);

        static::assertSame('3', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::CursorDown, $sequence->kind);
        static::assertSame("\e[3B", $sequence->toString());
    }

    public function testForward(): void
    {
        $sequence = Cursor\forward(10);

        static::assertSame('10', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::CursorForward, $sequence->kind);
        static::assertSame("\e[10C", $sequence->toString());
    }

    public function testBack(): void
    {
        $sequence = Cursor\back(2);

        static::assertSame('2', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::CursorBack, $sequence->kind);
        static::assertSame("\e[2D", $sequence->toString());
    }

    public function testMoveTo(): void
    {
        $sequence = Cursor\move_to(10, 20);

        static::assertSame('10;20', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::CursorMoveTo, $sequence->kind);
        static::assertSame("\e[10;20H", $sequence->toString());
    }

    public function testSave(): void
    {
        $sequence = Cursor\save();

        static::assertSame('', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SaveCursor, $sequence->kind);
        static::assertSame("\e[s", $sequence->toString());
    }

    public function testRestore(): void
    {
        $sequence = Cursor\restore();

        static::assertSame('', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::RestoreCursor, $sequence->kind);
        static::assertSame("\e[u", $sequence->toString());
    }

    public function testHide(): void
    {
        $sequence = Cursor\hide();

        static::assertSame('?25', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ResetMode, $sequence->kind);
        static::assertSame("\e[?25l", $sequence->toString());
    }

    public function testShow(): void
    {
        $sequence = Cursor\show();

        static::assertSame('?25', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SetMode, $sequence->kind);
        static::assertSame("\e[?25h", $sequence->toString());
    }

    public function testRequestPosition(): void
    {
        $sequence = Cursor\request_position();

        static::assertSame('6', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::DeviceStatusReport, $sequence->kind);
        static::assertSame("\e[6n", $sequence->toString());
    }
}
