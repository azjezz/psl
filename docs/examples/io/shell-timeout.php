<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Shell;

try {
    $output = Shell\execute('sleep', ['10'], cancellation: new Async\TimeoutCancellationToken(Duration::seconds(1)));
} catch (Async\Exception\CancelledException) {
    IO\write_line('Command exceeded the time limit (as expected)');
}
