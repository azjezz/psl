<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\Channel;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

/**
 * @var Channel\ReceiverInterface<string> $receiver
 * @var Channel\SenderInterface<string> $sender
 */
[$receiver, $sender] = Channel\unbounded();

Async\Scheduler::delay(1, static function () use ($sender) {
    $sender->send('Hello, World!');
});

$message = $receiver->receive();

IO\write_error_line($message);
