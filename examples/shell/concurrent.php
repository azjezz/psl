<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\IO;
use Psl\Shell;
use Psl\DateTime;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $start = DateTime\Timestamp::monotonic();

    Async\concurrently([
        static fn(): string => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn(): string => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn(): string => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn(): string => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn(): string => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
        static fn(): string => Shell\execute(PHP_BINARY, ['-r', '$t = time(); while(time() < ($t+1)) { echo "."; }']),
    ]);

    $duration = DateTime\Timestamp::monotonic()->since($start);

    IO\write_error_line('duration: %s.', $duration->toString());

    return 0;
});
