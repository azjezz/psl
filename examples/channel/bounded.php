<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\Channel;
use Psl\File;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function () {
    /**
     * @var Channel\ReceiverInterface<string> $receiver
     * @var Channel\SenderInterface<string> $sender
     */
    [$receiver, $sender] = Channel\bounded(10);

    Async\Scheduler::defer(static function () use ($receiver) {
        try {
            while (true) {
                $receiver->receive();
            }
        } catch (Channel\Exception\ClosedChannelException) {
            IO\write_error_line("[ receiver ]: completed.");
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

    IO\write_error_line("[ sender   ]: completed.");
    $sender->close();

    return 0;
});

