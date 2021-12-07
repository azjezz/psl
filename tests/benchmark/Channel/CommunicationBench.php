<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Channel;

use PhpBench\Attributes\Groups;
use Psl\Async;
use Psl\Channel;
use Psl\File;
use Psl\IO;

#[Groups(['channel'])]
final class CommunicationBench
{
    public function benchBoundedCommunication(): void
    {
        /**
         * @psalm-suppress MissingThrowsDocblock - $capacity is always > 0
         */
        [$receiver, $sender] = Channel\bounded(10);

        Async\Scheduler::defer(static function () use ($receiver) {
            try {
                while (true) {
                    $receiver->receive();
                }
            } catch (Channel\Exception\ClosedChannelException) {
                return;
            }
        });

        /** @psalm-suppress MissingThrowsDocblock */
        $file = File\open_read_only(__FILE__);
        $reader = new IO\Reader($file);
        /** @psalm-suppress MissingThrowsDocblock */
        while (!$reader->isEndOfFile()) {
            $byte = $reader->readByte();

            /** @psalm-suppress InvalidArgument */
            $sender->send($byte);
        }

        $sender->close();

        Async\Scheduler::run();
    }

    public function benchUnboundedCommunication(): void
    {
        /**
         * @psalm-suppress MissingThrowsDocblock - $capacity is always > 0
         */
        [$receiver, $sender] = Channel\bounded(10);

        Async\Scheduler::defer(static function () use ($receiver) {
            try {
                while (true) {
                    $receiver->receive();
                }
            } catch (Channel\Exception\ClosedChannelException) {
                return;
            }
        });

        /** @psalm-suppress MissingThrowsDocblock */
        $file = File\open_read_only(__FILE__);
        $reader = new IO\Reader($file);
        /** @psalm-suppress MissingThrowsDocblock */
        while (!$reader->isEndOfFile()) {
            $byte = $reader->readByte();

            /** @psalm-suppress InvalidArgument */
            $sender->send($byte);
        }

        $sender->close();

        Async\Scheduler::run();
    }
}
