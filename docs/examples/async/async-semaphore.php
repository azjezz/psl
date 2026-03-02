<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$semaphore = new Async\Semaphore(2, static function (int $input): void {
    IO\write_error_line('> started : %d', $input);
    Async\sleep(Duration::seconds(1));
    IO\write_error_line('> finished: %d', $input);
});

Async\concurrently([
    fn() => $semaphore->waitFor(1),
    fn() => $semaphore->waitFor(2),
    fn() => $semaphore->waitFor(3),
]);

// Output:
// > started: 1
// > started: 2
// > finished: 1
// > started: 3
// > finished: 2
// > finished: 3

$semaphore->cancel(new Exception('shutting down'));
