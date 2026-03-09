<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi\Screen;
use Psl\IO;

// Erasing and scrolling
IO\write(Screen\erase(Screen\EraseMode::Full)->toString()); // erase entire screen
IO\write(Screen\erase_line(Screen\LineEraseMode::Right)->toString()); // erase from cursor to end of line
IO\write(Screen\scroll_up(5)->toString()); // scroll viewport up

// Window properties
IO\write(Screen\title('My App')->toString()); // set window title
IO\write(Screen\notify('Build complete')->toString()); // desktop notification

// Progress indicator (supported by Windows Terminal, ConEmu, Kitty, Ghostty)
IO\write(Screen\progress(Screen\ProgressState::Normal, 50)->toString()); // 50% progress
IO\write(Screen\progress(Screen\ProgressState::Indeterminate)->toString()); // animated spinner
IO\write(Screen\progress_clear()->toString()); // remove indicator

// Terminal modes
IO\write(Screen\set_mode(Screen\ScreenMode::AlternateScreen)->toString()); // alternate screen buffer for TUIs
IO\write(Screen\set_mode(Screen\ScreenMode::MouseTracking)->toString()); // mouse click and scroll events
IO\write(Screen\set_mode(Screen\ScreenMode::BracketedPaste)->toString()); // distinguish pasted text from typed input
