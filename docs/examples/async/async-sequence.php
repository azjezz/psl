<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$sequence = new Async\Sequence::<int, void>(static function (int $input): void {
    IO\write_error_line('> started : %d', $input);
    Async\sleep(Duration::seconds(1));
    IO\write_error_line('> finished: %d', $input);
});

Async\concurrently::<int, void>([
    fn() => $sequence->waitFor(1),
    fn() => $sequence->waitFor(2),
    fn() => $sequence->waitFor(3),
]);

// Output:
// > started: 1
// > finished: 1
// > started: 2
// > finished: 2
// > started: 3
// > finished: 3
