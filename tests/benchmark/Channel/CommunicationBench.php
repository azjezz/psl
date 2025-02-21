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
    /**
     * @throws Channel\Exception\ExceptionInterface
     * @throws IO\Exception\ExceptionInterface
     * @throws File\Exception\ExceptionInterface
     *
     * @psalm-suppress UnnecessaryVarAnnotation
     */
    public function benchBoundedCommunication(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(10);

        Async\Scheduler::defer(static function () use ($receiver): void {
            try {
                while (true) {
                    $receiver->receive();
                }
            } catch (Channel\Exception\ClosedChannelException) {
                return;
            }
        });

        $file = File\open_read_only(__FILE__);
        do {
            $byte = $file->readAll(1);
            if ('' === $byte) {
                break;
            }

            $sender->send($byte);
        } while (true);

        $sender->close();

        Async\Scheduler::run();
    }

    /**
     * @throws Channel\Exception\ExceptionInterface
     * @throws IO\Exception\ExceptionInterface
     * @throws File\Exception\ExceptionInterface
     *
     * @psalm-suppress UnnecessaryVarAnnotation
     */
    public function benchUnboundedCommunication(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(10);

        Async\Scheduler::defer(static function () use ($receiver): void {
            try {
                while (true) {
                    $receiver->receive();
                }
            } catch (Channel\Exception\ClosedChannelException) {
                return;
            }
        });

        $file = File\open_read_only(__FILE__);
        do {
            $byte = $file->readAll(1);
            if ('' === $byte) {
                break;
            }

            $sender->send($byte);
        } while (true);

        $sender->close();

        Async\Scheduler::run();
    }
}
