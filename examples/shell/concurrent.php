<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $start = time();

    Async\concurrent([
        static fn() => Shell\execute('sleep', ['2']),
        static fn() => Shell\execute('sleep', ['2']),
        static fn() => Shell\execute('sleep', ['1']),
    ]);

    $duration = time() - $start;

    IO\output_handle()->writeAll("duration: $duration.\n");

    return 0;
});
