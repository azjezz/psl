<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;

$sequence = new Async\KeyedSequence(static function (string $key, int $input): void {
    Async\sleep(Duration::seconds(1));
});

Async\concurrently([
    fn() => $sequence->waitFor('foo', 1), // starts immediately
    fn() => $sequence->waitFor('foo', 2), // waits for foo:1
    fn() => $sequence->waitFor('bar', 1), // starts immediately (different key)
]);
