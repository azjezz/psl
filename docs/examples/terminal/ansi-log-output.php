<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;

IO\write_line(Ansi\apply('Error: something went wrong', Style\bold(), Ansi\foreground(Color\red())));
IO\write_line(Ansi\apply('Warning: check configuration', Ansi\foreground(Color\yellow())));
IO\write_line(Ansi\apply('Success!', Style\bold(), Ansi\foreground(Color\green())));
