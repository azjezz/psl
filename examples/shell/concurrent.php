<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

$start = time();

Async\concurrently([
    static fn() => Shell\execute('sleep', ['2']),
    static fn() => Shell\execute('sleep', ['2']),
    static fn() => Shell\execute('sleep', ['1']),
])->await();

$duration = time() - $start;

IO\output_handle()->writeAll("duration: $duration.\n");
