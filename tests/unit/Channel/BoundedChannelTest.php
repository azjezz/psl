<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Channel;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Channel;

final class BoundedChannelTest extends TestCase
{
    public function testCapacity(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        static::assertSame(1, $receiver->getCapacity());
        static::assertSame(1, $sender->getCapacity());
    }

    public function testCloseSender(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        static::assertFalse($receiver->isClosed());
        static::assertFalse($sender->isClosed());

        $sender->close();

        static::assertTrue($receiver->isClosed());
        static::assertTrue($sender->isClosed());
    }

    public function testCloseReceiver(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        static::assertFalse($receiver->isClosed());
        static::assertFalse($sender->isClosed());

        $receiver->close();

        static::assertTrue($receiver->isClosed());
        static::assertTrue($sender->isClosed());
    }

    public function testCount(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(2);

        static::assertSame(0, $receiver->count());
        static::assertSame(0, $sender->count());

        $sender->send('foo');
        $sender->send('bar');

        static::assertSame(2, $receiver->count());
        static::assertSame(2, $sender->count());

        static::assertSame('foo', $receiver->receive());

        static::assertSame(1, $receiver->count());
        static::assertSame(1, $sender->count());

        static::assertSame('bar', $receiver->tryReceive());

        static::assertSame(0, $receiver->count());
        static::assertSame(0, $sender->count());

        $sender->trySend('baz');

        static::assertSame(1, $receiver->count());
        static::assertSame(1, $sender->count());
    }

    public function testIsFull(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        static::assertFalse($receiver->isFull());
        static::assertFalse($sender->isFull());

        $sender->send('foo');

        static::assertTrue($receiver->isFull());
        static::assertTrue($sender->isFull());
    }

    public function testTrySendThrowsOnFullChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $sender->send('hello');

        $this->expectException(Channel\Exception\FullChannelException::class);

        $sender->trySend('world');
    }

    public function testSendWaitsForFullChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $sender->send('hello');

        Async\Scheduler::delay(0.001, static function () use ($receiver) {
            $receiver->receive();
        });

        static::assertTrue($receiver->isFull());

        $sender->send('world');
    }

    public function testSendThrowsForClosedChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $receiver->close();

        $this->expectException(Channel\Exception\ClosedChannelException::class);

        $sender->send('world');
    }

    public function testSendThrowsForLateClosedChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $sender->send('hello');

        Async\Scheduler::delay(0.001, static function () use ($receiver): void {
            $receiver->close();
        });

        $this->expectException(Channel\Exception\ClosedChannelException::class);

        $sender->send('world');
    }

    public function testTrySendThrowsForClosedChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $receiver->close();

        $this->expectException(Channel\Exception\ClosedChannelException::class);

        $sender->trySend('world');
    }

    public function testReceiveThrowsForClosedChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $sender->close();

        $this->expectException(Channel\Exception\ClosedChannelException::class);

        $receiver->receive();
    }

    public function testReceiveThrowsForLateClosedChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        Async\Scheduler::delay(0.0001, static function () use ($sender): void {
            $sender->close();
        });

        $this->expectException(Channel\Exception\ClosedChannelException::class);

        $receiver->receive();
    }

    public function testTryReceiveThrowsForClosedChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $sender->close();

        $this->expectException(Channel\Exception\ClosedChannelException::class);

        $receiver->tryReceive();
    }

    public function testReceiveWaitsWhenChannelIsEmpty(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        Async\Scheduler::delay(0.001, static function () use ($sender) {
            $sender->send('hello');
        });

        static::assertTrue($receiver->isEmpty());

        static::assertSame('hello', $receiver->receive());
    }

    public function testTryReceiveThrowsForEmptyChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $this->expectException(Channel\Exception\EmptyChannelException::class);

        $receiver->tryReceive();
    }
}
