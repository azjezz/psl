<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;

// Combine multiple styles
IO\write(Ansi\apply('important', Style\bold(), Style\underline(), Ansi\foreground(Color\red())));
IO\write("\n");

// Background colors
IO\write(Ansi\apply(' PASS ', Style\bold(), Ansi\foreground(Color\white()), Ansi\background(Color\green())));
IO\write("\n");
