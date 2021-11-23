<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\Channel;
use Psl\File;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

/**
 * @var Channel\ReceiverInterface<string> $receiver
 * @var Channel\SenderInterface<string> $sender
 */
[$receiver, $sender] = Channel\bounded(10);

Async\Scheduler::defer(static function () use ($receiver) {
    try {
        while (true) {
            $receiver->receive();

            IO\output_handle()->writeAll('.');
        }
    } catch (Channel\Exception\ClosedChannelException) {
        return;
    }
});

for ($i = 0; $i < 10; $i++) {
    $file = File\open_read_only(__FILE__);
    $reader = new IO\Reader($file);
    while (!$reader->isEndOfFile()) {
        $byte = $reader->readByte();

        $sender->send($byte);
    }
}

IO\output_handle()->writeAll("\ndone\n");
