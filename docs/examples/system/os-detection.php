<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\OS;

if (OS\is_windows()) {
    IO\write_line('Running on Windows.');
}

if (OS\is_darwin()) {
    IO\write_line('Running on macOS.');
}

if (!OS\is_windows() && !OS\is_darwin()) {
    IO\write_line('Running on Linux or another platform.');
}
