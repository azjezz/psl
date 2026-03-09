<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Cursor;
use Psl\Ansi\Screen;
use Psl\Ansi\Style;
use Psl\IO;

// Style and color text
IO\write(Ansi\apply('Hello, world!', Style\bold(), Ansi\foreground(Color\green())));
IO\write("\n");

// Cursor movement
IO\write(Cursor\move_to(1, 1)->toString());
IO\write(Cursor\hide()->toString());

// Screen control
IO\write(Screen\erase(Screen\EraseMode::Full)->toString());
IO\write(Screen\title('My App')->toString());

// Hyperlinks (OSC 8)
IO\write(Ansi\link('Click here', 'https://example.com', Style\underline()));
IO\write("\n");

// Strip ANSI sequences from text
$plain = Ansi\strip("\e[1mBold\e[0m"); // "Bold"
IO\write_line('Stripped: %s', $plain);
