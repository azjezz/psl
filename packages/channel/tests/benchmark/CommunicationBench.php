<?php

declare(strict_types=1);

namespace Psl\Channel\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use Psl\Async;
use Psl\Channel;

use function fclose;
use function fopen;
use function fread;

#[Groups(['channel'])]
final class CommunicationBench
{
    /**
     * @throws Channel\Exception\ExceptionInterface
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

        $handle = fopen(__FILE__, 'rb');
        do {
            $byte = fread($handle, 1);
            if ($byte === '' || $byte === false) {
                break;
            }

            $sender->send($byte);
        } while (true);

        fclose($handle);

        $sender->close();

        Async\Scheduler::run();
    }

    /**
     * @throws Channel\Exception\ExceptionInterface
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

        $handle = fopen(__FILE__, 'rb');
        do {
            $byte = fread($handle, 1);
            if ($byte === '' || $byte === false) {
                break;
            }

            $sender->send($byte);
        } while (true);

        fclose($handle);

        $sender->close();

        Async\Scheduler::run();
    }
}
