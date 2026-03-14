<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$wg = new Async\WaitGroup();

for ($i = 0; $i < 3; $i++) {
    $wg->add();
    Async\run(static function () use ($wg, $i): void {
        Async\sleep(Duration::milliseconds(10 * ($i + 1)));
        IO\write_line('Task %d done', $i);
        $wg->done();
    })->ignore();
}

$wg->wait();
IO\write_line('All tasks done');
