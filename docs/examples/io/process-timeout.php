<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Process\Command;
use Psl\Process\Exception;

try {
    Command::create('sleep')->withArgument('60')->status(Duration::seconds(1));
} catch (Exception\TimeoutException) {
    IO\write_line('Process was killed after timeout (as expected)');
}
