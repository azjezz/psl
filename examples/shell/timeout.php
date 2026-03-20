<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

try {
    Shell\execute('sleep', ['1'], cancellation: new Async\TimeoutCancellationToken(Duration::milliseconds(500)));
} catch (Async\Exception\CancelledException $exception) {
    IO\write_error_line($exception->getMessage());
}
