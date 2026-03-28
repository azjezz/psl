<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;

$semaphore = new Async\KeyedSemaphore(2, static function (string $_key, int $_input): void {
    Async\sleep(Duration::seconds(1));
});

Async\concurrently([
    fn() => $semaphore->waitFor('foo', 1), // starts immediately
    fn() => $semaphore->waitFor('foo', 2), // starts immediately (limit 2 for 'foo')
    fn() => $semaphore->waitFor('foo', 3), // waits for one 'foo' to finish
    fn() => $semaphore->waitFor('bar', 1), // starts immediately (separate key)
]);
