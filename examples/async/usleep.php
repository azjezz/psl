<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $start = time();

    Async\concurrent([
        static fn() => Async\usleep(2_000_000),
        static fn() => Async\usleep(2_000_000),
        static fn() => Async\usleep(2_000_000),
    ]);

    $duration = time() - $start;

    IO\output_handle()->writeAll("duration: $duration\n");

    return 0;
});
