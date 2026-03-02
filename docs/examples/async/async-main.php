<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

Async\main(static function (): int {
    Async\Scheduler::delay(Duration::seconds(1), static function (): void {
        IO\write_line('hello');
    });

    return 0;
});

// Output:
// hello
