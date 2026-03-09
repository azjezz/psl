<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Shell;

try {
    $output = Shell\execute('php', ['-r', 'exit(1);']);
} catch (Shell\Exception\FailedExecutionException $e) {
    IO\write_line('Command failed: %s', $e->getCommand());
    IO\write_line('Exit code: %d', $e->getCode());
    IO\write_line('Stderr: %s', $e->getErrorOutput());
}
