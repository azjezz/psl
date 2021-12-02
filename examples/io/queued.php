<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl;
use Psl\Async;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function(): int {
    [$read, $write] = IO\pipe();

    $he = Async\run(static fn(): string => $read->readFixedSize(2));

    Async\sleep(0.001);

    $write->write("hello");

    $llo = $read->readFixedSize(3);

    Psl\invariant($he->isComplete(), 'First read should have completed before second one.');
    Psl\invariant('llo' === $llo, 'First read should have completed before second one.');

    IO\write_error_line($he->await() . $llo);

    return 0;
});
