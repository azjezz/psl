<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

$msg = 'file not found';

IO\write('Hello, %s!', 'world'); // stdout, no newline
IO\write_line('Count: %d', 42); // stdout + newline
IO\write_error('something went wrong'); // stderr
IO\write_error_line('Error: %s', $msg); // stderr + newline
