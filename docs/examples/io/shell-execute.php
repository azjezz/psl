<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Shell;

// Run a command and capture stdout
$output = Shell\execute('echo', ['Hello from shell']);
IO\write_line('Output: %s', $output);

// With a specific working directory
$output = Shell\execute('ls', ['-la'], __DIR__);
IO\write_line('Directory listing:\n%s', $output);

// With environment variables
$output = Shell\execute(
    'php',
    ['-r', 'echo getenv("APP_ENV");'],
    environment: [
        'APP_ENV' => 'production',
        'DEBUG' => '0',
    ],
);
IO\write_line('Env value: %s', $output);
