<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;

$output = Command::create('php')->withArguments(['-r', 'echo "hello world";'])->output();

IO\write_line('stdout: %s', $output->stdout); // "hello world"
IO\write_line('stderr: %s', $output->stderr); // ""
