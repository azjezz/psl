<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\ControlSequenceIntroducerKind;

final class ControlSequenceIntroducerKindTest extends TestCase
{
    public function testSelectGraphicRendition(): void
    {
        static::assertSame('m', ControlSequenceIntroducerKind::SelectGraphicRendition->value);
    }

    public function testCursorUp(): void
    {
        static::assertSame('A', ControlSequenceIntroducerKind::CursorUp->value);
    }

    public function testCursorDown(): void
    {
        static::assertSame('B', ControlSequenceIntroducerKind::CursorDown->value);
    }

    public function testCursorForward(): void
    {
        static::assertSame('C', ControlSequenceIntroducerKind::CursorForward->value);
    }

    public function testCursorBack(): void
    {
        static::assertSame('D', ControlSequenceIntroducerKind::CursorBack->value);
    }

    public function testCursorMoveTo(): void
    {
        static::assertSame('H', ControlSequenceIntroducerKind::CursorMoveTo->value);
    }

    public function testEraseInDisplay(): void
    {
        static::assertSame('J', ControlSequenceIntroducerKind::EraseInDisplay->value);
    }

    public function testEraseInLine(): void
    {
        static::assertSame('K', ControlSequenceIntroducerKind::EraseInLine->value);
    }

    public function testScrollUp(): void
    {
        static::assertSame('S', ControlSequenceIntroducerKind::ScrollUp->value);
    }

    public function testScrollDown(): void
    {
        static::assertSame('T', ControlSequenceIntroducerKind::ScrollDown->value);
    }

    public function testSaveCursor(): void
    {
        static::assertSame('s', ControlSequenceIntroducerKind::SaveCursor->value);
    }

    public function testRestoreCursor(): void
    {
        static::assertSame('u', ControlSequenceIntroducerKind::RestoreCursor->value);
    }

    public function testSetMode(): void
    {
        static::assertSame('h', ControlSequenceIntroducerKind::SetMode->value);
    }

    public function testResetMode(): void
    {
        static::assertSame('l', ControlSequenceIntroducerKind::ResetMode->value);
    }

    public function testDeviceStatusReport(): void
    {
        static::assertSame('n', ControlSequenceIntroducerKind::DeviceStatusReport->value);
    }
}
