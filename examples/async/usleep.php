<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $start = time();

    Async\concurrently([
        static fn() => Async\sleep(2.0),
        static fn() => Async\sleep(2.0),
        static fn() => Async\sleep(2.0),
    ]);

    $duration = time() - $start;

    IO\write_error_line("duration: %d.", $duration);

    return 0;
});
