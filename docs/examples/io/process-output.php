<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;

// Run a command and collect its output
$output = Command::create('echo')->withArguments(['Hello', 'from', 'process'])->output();

if ($output->status->isSuccessful()) {
    IO\write_line('Output: %s', trim($output->stdout));
}
