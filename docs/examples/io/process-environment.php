<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;

$output = Command::create('php')
    ->withArguments(['-r', 'echo getenv("APP_ENV");'])
    ->withEnvironmentVariable('APP_ENV', 'production')
    ->output();

IO\write_line('APP_ENV: %s', $output->stdout); // "production"
