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

// Spawn 3 worker consumers
for ($i = 0; $i < 3; $i++) {
    Async\run(function () use ($receiver, $i): void {
        while (!$receiver->isClosed() || !$receiver->isEmpty()) {
            $task = $receiver->receive();
            IO\write_error_line('Worker %d processing: %s', $i, $task);
        }
    });
}

// Feed tasks
$tasks = ['task-a', 'task-b', 'task-c', 'task-d', 'task-e'];
foreach ($tasks as $task) {
    $sender->send($task);
}

$sender->close();

Async\later();
