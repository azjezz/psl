<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\DateTime;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

$start = DateTime\Timestamp::monotonic();

Async\concurrently([
    static fn(): null => Async\sleep(DateTime\Duration::hours(0)),
    static fn(): null => Async\sleep(DateTime\Duration::minutes(0)),
    static fn(): null => Async\sleep(DateTime\Duration::zero()),
    static fn(): null => Async\sleep(DateTime\Duration::seconds(2)),
    static fn(): null => Async\sleep(DateTime\Duration::nanoseconds(20_000_000)),
    static fn(): null => Async\sleep(DateTime\Duration::microseconds(200_000)),
    static fn(): null => Async\sleep(DateTime\Duration::milliseconds(2000)),
]);

$duration = DateTime\Timestamp::monotonic()->since($start);

IO\write_error_line('duration : %s.', $duration->toString(maxDecimals: 5));
