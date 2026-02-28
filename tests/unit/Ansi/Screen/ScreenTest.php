<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Ansi\Screen;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\ControlSequenceIntroducerKind;
use Psl\Ansi\OperatingSystemCommandKind;
use Psl\Ansi\Screen;

final class ScreenTest extends TestCase
{
    public function testEraseDefault(): void
    {
        $sequence = Screen\erase();

        static::assertSame('0', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::EraseInDisplay, $sequence->kind);
        static::assertSame("\e[0J", $sequence->toString());
    }

    public function testEraseFull(): void
    {
        $sequence = Screen\erase(Screen\EraseMode::Full);

        static::assertSame('2', $sequence->parameters);
        static::assertSame("\e[2J", $sequence->toString());
    }

    public function testEraseAbove(): void
    {
        $sequence = Screen\erase(Screen\EraseMode::Above);

        static::assertSame('1', $sequence->parameters);
    }

    public function testEraseFullWithScrollback(): void
    {
        $sequence = Screen\erase(Screen\EraseMode::FullWithScrollback);

        static::assertSame('3', $sequence->parameters);
    }

    public function testEraseLineDefault(): void
    {
        $sequence = Screen\erase_line();

        static::assertSame('0', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::EraseInLine, $sequence->kind);
        static::assertSame("\e[0K", $sequence->toString());
    }

    public function testEraseLineFull(): void
    {
        $sequence = Screen\erase_line(Screen\LineEraseMode::Full);

        static::assertSame('2', $sequence->parameters);
        static::assertSame("\e[2K", $sequence->toString());
    }

    public function testEraseLineLeft(): void
    {
        $sequence = Screen\erase_line(Screen\LineEraseMode::Left);

        static::assertSame('1', $sequence->parameters);
    }

    public function testScrollUp(): void
    {
        $sequence = Screen\scroll_up(5);

        static::assertSame('5', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ScrollUp, $sequence->kind);
        static::assertSame("\e[5S", $sequence->toString());
    }

    public function testScrollUpDefault(): void
    {
        $sequence = Screen\scroll_up();

        static::assertSame('1', $sequence->parameters);
    }

    public function testScrollDown(): void
    {
        $sequence = Screen\scroll_down(3);

        static::assertSame('3', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ScrollDown, $sequence->kind);
        static::assertSame("\e[3T", $sequence->toString());
    }

    public function testScrollDownDefault(): void
    {
        $sequence = Screen\scroll_down();

        static::assertSame('1', $sequence->parameters);
    }

    public function testEraseModeValues(): void
    {
        static::assertSame(0, Screen\EraseMode::Below->value);
        static::assertSame(1, Screen\EraseMode::Above->value);
        static::assertSame(2, Screen\EraseMode::Full->value);
        static::assertSame(3, Screen\EraseMode::FullWithScrollback->value);
    }

    public function testLineEraseModeValues(): void
    {
        static::assertSame(0, Screen\LineEraseMode::Right->value);
        static::assertSame(1, Screen\LineEraseMode::Left->value);
        static::assertSame(2, Screen\LineEraseMode::Full->value);
    }

    public function testTitle(): void
    {
        $osc = Screen\title('My App');

        static::assertSame(OperatingSystemCommandKind::WindowTitle, $osc->kind);
        static::assertSame('My App', $osc->data);
        static::assertSame("\e]2;My App\e\\", $osc->toString());
    }

    public function testIcon(): void
    {
        $osc = Screen\icon('myicon');

        static::assertSame(OperatingSystemCommandKind::WindowIcon, $osc->kind);
        static::assertSame('myicon', $osc->data);
        static::assertSame("\e]1;myicon\e\\", $osc->toString());
    }

    public function testIconAndTitle(): void
    {
        $osc = Screen\icon_and_title('My App');

        static::assertSame(OperatingSystemCommandKind::WindowIconAndTitle, $osc->kind);
        static::assertSame('My App', $osc->data);
        static::assertSame("\e]0;My App\e\\", $osc->toString());
    }

    public function testNotify(): void
    {
        $osc = Screen\notify('Build complete');

        static::assertSame(OperatingSystemCommandKind::Notify, $osc->kind);
        static::assertSame('Build complete', $osc->data);
        static::assertSame("\e]9;Build complete\e\\", $osc->toString());
    }

    public function testChangeDirectory(): void
    {
        $osc = Screen\change_directory('/home/user');

        static::assertSame(OperatingSystemCommandKind::ChangeDirectory, $osc->kind);
        static::assertSame('/home/user', $osc->data);
        static::assertSame("\e]7;/home/user\e\\", $osc->toString());
    }

    public function testClipboard(): void
    {
        $osc = Screen\clipboard('hello');

        static::assertSame(OperatingSystemCommandKind::Clipboard, $osc->kind);
        static::assertSame('c;aGVsbG8=', $osc->data);
        static::assertSame("\e]52;c;aGVsbG8=\e\\", $osc->toString());
    }

    public function testEnableMouseTracking(): void
    {
        $sequence = Screen\enable_mouse_tracking();

        static::assertSame('?1000;?1006', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SetMode, $sequence->kind);
        static::assertSame("\e[?1000;?1006h", $sequence->toString());
    }

    public function testDisableMouseTracking(): void
    {
        $sequence = Screen\disable_mouse_tracking();

        static::assertSame('?1006;?1000', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ResetMode, $sequence->kind);
        static::assertSame("\e[?1006;?1000l", $sequence->toString());
    }

    public function testEnableBracketedPaste(): void
    {
        $sequence = Screen\enable_bracketed_paste();

        static::assertSame('?2004', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SetMode, $sequence->kind);
        static::assertSame("\e[?2004h", $sequence->toString());
    }

    public function testDisableBracketedPaste(): void
    {
        $sequence = Screen\disable_bracketed_paste();

        static::assertSame('?2004', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ResetMode, $sequence->kind);
        static::assertSame("\e[?2004l", $sequence->toString());
    }

    public function testEnableAlternateScreen(): void
    {
        $sequence = Screen\enable_alternate_screen();

        static::assertSame('?1049', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SetMode, $sequence->kind);
        static::assertSame("\e[?1049h", $sequence->toString());
    }

    public function testDisableAlternateScreen(): void
    {
        $sequence = Screen\disable_alternate_screen();

        static::assertSame('?1049', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ResetMode, $sequence->kind);
        static::assertSame("\e[?1049l", $sequence->toString());
    }

    public function testBracketedPasteStart(): void
    {
        static::assertSame("\e[200~", Screen\bracketed_paste_start());
    }

    public function testBracketedPasteEnd(): void
    {
        static::assertSame("\e[201~", Screen\bracketed_paste_end());
    }
}
