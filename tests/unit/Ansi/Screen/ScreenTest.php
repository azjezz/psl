<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Ansi\Screen;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
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

        static::assertSame('?1000;1006', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SetMode, $sequence->kind);
        static::assertSame("\e[?1000;1006h", $sequence->toString());
    }

    public function testDisableMouseTracking(): void
    {
        $sequence = Screen\disable_mouse_tracking();

        static::assertSame('?1006;1000', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ResetMode, $sequence->kind);
        static::assertSame("\e[?1006;1000l", $sequence->toString());
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

    public function testProgressNormal(): void
    {
        $osc = Screen\progress(Screen\ProgressState::Normal, 50);

        static::assertSame(OperatingSystemCommandKind::Notify, $osc->kind);
        static::assertSame('4;1;50', $osc->data);
        static::assertSame("\e]9;4;1;50\e\\", $osc->toString());
    }

    public function testProgressError(): void
    {
        $osc = Screen\progress(Screen\ProgressState::Error, 75);

        static::assertSame('4;2;75', $osc->data);
        static::assertSame("\e]9;4;2;75\e\\", $osc->toString());
    }

    public function testProgressIndeterminate(): void
    {
        $osc = Screen\progress(Screen\ProgressState::Indeterminate);

        static::assertSame('4;3;0', $osc->data);
        static::assertSame("\e]9;4;3;0\e\\", $osc->toString());
    }

    public function testProgressWarning(): void
    {
        $osc = Screen\progress(Screen\ProgressState::Warning, 25);

        static::assertSame('4;4;25', $osc->data);
        static::assertSame("\e]9;4;4;25\e\\", $osc->toString());
    }

    public function testProgressZero(): void
    {
        $osc = Screen\progress(Screen\ProgressState::Normal, 0);

        static::assertSame('4;1;0', $osc->data);
        static::assertSame("\e]9;4;1;0\e\\", $osc->toString());
    }

    public function testProgressFull(): void
    {
        $osc = Screen\progress(Screen\ProgressState::Normal, 100);

        static::assertSame('4;1;100', $osc->data);
        static::assertSame("\e]9;4;1;100\e\\", $osc->toString());
    }

    public function testProgressClear(): void
    {
        $osc = Screen\progress_clear();

        static::assertSame(OperatingSystemCommandKind::Notify, $osc->kind);
        static::assertSame('4;0', $osc->data);
        static::assertSame("\e]9;4;0\e\\", $osc->toString());
    }

    public function testProgressStateValues(): void
    {
        static::assertSame(1, Screen\ProgressState::Normal->value);
        static::assertSame(2, Screen\ProgressState::Error->value);
        static::assertSame(3, Screen\ProgressState::Indeterminate->value);
        static::assertSame(4, Screen\ProgressState::Warning->value);
    }

    public function testBracketedPasteStart(): void
    {
        static::assertSame("\e[200~", Screen\bracketed_paste_start());
    }

    public function testBracketedPasteEnd(): void
    {
        static::assertSame("\e[201~", Screen\bracketed_paste_end());
    }

    public function testEnableFocusTracking(): void
    {
        $sequence = Screen\enable_focus_tracking();

        static::assertSame('?1004', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SetMode, $sequence->kind);
        static::assertSame("\e[?1004h", $sequence->toString());
    }

    public function testDisableFocusTracking(): void
    {
        $sequence = Screen\disable_focus_tracking();

        static::assertSame('?1004', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::ResetMode, $sequence->kind);
        static::assertSame("\e[?1004l", $sequence->toString());
    }

    public function testEnableKittyKeyboard(): void
    {
        $sequence = Screen\enable_kitty_keyboard();

        static::assertSame('>1', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::RestoreCursor, $sequence->kind);
        static::assertSame("\e[>1u", $sequence->toString());
    }

    public function testEnableKittyKeyboardWithFlags(): void
    {
        $sequence = Screen\enable_kitty_keyboard(3);

        static::assertSame('>3', $sequence->parameters);
        static::assertSame("\e[>3u", $sequence->toString());
    }

    public function testDisableKittyKeyboard(): void
    {
        $sequence = Screen\disable_kitty_keyboard();

        static::assertSame('<', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::RestoreCursor, $sequence->kind);
        static::assertSame("\e[<u", $sequence->toString());
    }

    public function testBell(): void
    {
        $bell = Ansi\bell();

        static::assertInstanceOf(Ansi\ControlCharacter::class, $bell);
        static::assertSame("\x07", $bell->toString());
        static::assertSame("\x07", (string) $bell);
    }
}
