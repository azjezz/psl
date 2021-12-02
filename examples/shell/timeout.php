<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): void {
    try {
        Shell\execute('sleep', ['1'], timeout: 0.5);
    } catch (Shell\Exception\TimeoutException $exception) {
        IO\write_error_line($exception->getMessage());
    }
});
