<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\DateTime;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    try {
        Shell\execute('sleep', ['1'], timeout: DateTime\Duration::milliseconds(500));
    } catch (Shell\Exception\TimeoutException $exception) {
        IO\write_error_line($exception->getMessage());
    }

    return 0;
});
