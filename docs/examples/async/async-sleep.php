<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;

$time = time();

Async\concurrently([
    static fn() => Async\sleep(Duration::seconds(2)),
    static fn() => Async\sleep(Duration::seconds(2)),
    static fn() => Async\sleep(Duration::seconds(2)),
]);

// Total time: ~2 seconds, not 6
