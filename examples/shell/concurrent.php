<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $start = time();

    Async\concurrently([
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn() => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
    ]);

    $duration = time() - $start;

    IO\write_error_line('duration: %d.', $duration);

    return 0;
});
