<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\DateTime;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): int {
    $start = DateTime\Timestamp::now();

    Async\concurrently([
        static fn() => Async\sleep(DateTime\Duration::hours(0)),
        static fn() => Async\sleep(DateTime\Duration::minutes(0)),
        static fn() => Async\sleep(DateTime\Duration::zero()),
        static fn() => Async\sleep(DateTime\Duration::seconds(2)),
        static fn() => Async\sleep(DateTime\Duration::nanoseconds(20000000)),
        static fn() => Async\sleep(DateTime\Duration::microseconds(200000)),
        static fn() => Async\sleep(DateTime\Duration::milliseconds(2000)),
    ]);

    $duration = DateTime\Timestamp::now()->since($start);

    IO\write_error_line("duration: %s.", $duration->toString(max_decimals: 2));

    return 0;
});
