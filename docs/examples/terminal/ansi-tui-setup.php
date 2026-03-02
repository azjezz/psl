<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Cursor;
use Psl\Ansi\Screen;
use Psl\IO;

// Enter TUI mode
IO\write(
    Screen\set_mode(Screen\ScreenMode::AlternateScreen)->toString()
        . Cursor\hide()->toString()
        . Screen\erase(Screen\EraseMode::Full)->toString()
        . Cursor\move_to(1, 1)->toString()
        . Screen\title('My TUI App')->toString(),
);

// ... render UI ...

// Exit TUI mode
IO\write(
    Cursor\show()->toString()
        . Ansi\reset()->toString()
        . Screen\reset_mode(Screen\ScreenMode::AlternateScreen)->toString()
        . Screen\title('')->toString(),
);
