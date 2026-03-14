<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Process\Command;

try {
    Command::create('sleep')->withArgument('60')->status(new Async\TimeoutCancellationToken(Duration::seconds(1)));
} catch (Async\Exception\CancelledException) {
    IO\write_line('Process was killed after timeout (as expected)');
}
