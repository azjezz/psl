<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;

$output = Command::shell('echo hello && echo world')->output();

IO\write_line('stdout: %s', $output->stdout); // "hello\nworld\n"
