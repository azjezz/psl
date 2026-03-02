<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Channel;
use Psl\IO;

/**
 * @var Channel\ReceiverInterface<string> $receiver
 * @var Channel\SenderInterface<string> $sender
 */
[$receiver, $sender] = Channel\bounded(100);

// Producer
Async\run(function () use ($sender): void {
    foreach (['job-1', 'job-2', 'job-3'] as $job) {
        $sender->send($job);
    }

    $sender->close();
});

// Consumer
Async\run(function () use ($receiver): void {
    while (!$receiver->isClosed() || !$receiver->isEmpty()) {
        $job = $receiver->receive();
        IO\write_error_line('Processing: %s', $job);
    }
});

Async\later();
