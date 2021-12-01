<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $start = time();

    Async\parallel([
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
    ]);

    $duration = time() - $start;

    IO\output_handle()->writeAll("duration: $duration.\n");

    return 0;
});
