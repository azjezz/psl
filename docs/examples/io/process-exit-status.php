<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;

$status = Command::create('php')->withArguments(['-r', 'exit(42);'])->status();

IO\write_line('Successful: %s', $status->isSuccessful() ? 'true' : 'false'); // false
IO\write_line('Exit code: %d', $status->getCode()); // 42
