<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Channel;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Channel;
use Psl\DateTime;

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
         * @var Channel\SenderInterface<string> $sender
         */
        [$_, $sender] = Channel\bounded(1);

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

        Async\Scheduler::delay(DateTime\Duration::milliseconds(1), static function () use ($receiver): void {
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
        $this->expectExceptionMessage('Attempted to send a message to a closed channel.');

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

        Async\Scheduler::delay(DateTime\Duration::milliseconds(1), static function () use ($receiver): void {
            $receiver->close();
        });

        $this->expectException(Channel\Exception\ClosedChannelException::class);
        $this->expectExceptionMessage('Attempted to send a message to a closed channel.');

        $sender->send('world');
    }

    public function testSendThrowsForLateClosedFullChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $sender->send('hello');
        $receiver->close();

        $this->expectException(Channel\Exception\ClosedChannelException::class);
        $this->expectExceptionMessage('Attempted to send a message to a closed channel.');

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
        $this->expectExceptionMessage('Attempted to send a message to a closed channel.');

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
        $this->expectExceptionMessage('Attempted to receive a message from a closed empty channel.');

        $receiver->receive();
    }

    public function testReceiveThrowsForLateClosedChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        Async\Scheduler::delay(DateTime\Duration::milliseconds(1), static function () use ($sender): void {
            $sender->close();
        });

        $this->expectException(Channel\Exception\ClosedChannelException::class);
        $this->expectExceptionMessage('Attempted to receive a message from a closed empty channel.');

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
        $this->expectExceptionMessage('Attempted to receive a message from a closed empty channel.');

        $receiver->tryReceive();
    }

    public function testReceiveWaitsWhenChannelIsEmpty(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        Async\Scheduler::delay(DateTime\Duration::milliseconds(1), static fn(): null => $sender->send('hello'));

        static::assertTrue($receiver->isEmpty());

        static::assertSame('hello', $receiver->receive());
    }

    public function testTryReceiveThrowsForEmptyChannel(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         */
        [$receiver, $_] = Channel\bounded(1);

        $this->expectException(Channel\Exception\EmptyChannelException::class);
        $this->expectExceptionMessage('Attempted to receiver from an empty channel.');

        $receiver->tryReceive();
    }

    public function testSendWaitsForPreviousOperationWhenChannelIsFull(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        // fill the channel.
        $sender->send('foo');

        $one = Async\run(static fn(): null => $sender->send('bar'));
        $two = Async\run(static fn(): null => $sender->send('baz'));

        Async\Scheduler::defer(static function () use ($receiver): void {
            $receiver->receive();
        });

        Async\Scheduler::defer(static function () use ($receiver): void {
            $receiver->receive();
        });

        $two->await();

        static::assertTrue($one->isComplete());

        $one->await();
    }

    public function testReceiveWaitsForPreviousOperationsWhenChannelIsEmpty(): void
    {
        /**
         * @var Channel\ReceiverInterface<string> $receiver
         * @var Channel\SenderInterface<string> $sender
         */
        [$receiver, $sender] = Channel\bounded(1);

        $one = Async\run(static fn(): string => $receiver->receive());
        $two = Async\run(static fn(): string => $receiver->receive());

        Async\Scheduler::defer(static fn(): null => $sender->send('foo'));
        Async\Scheduler::defer(static fn(): null => $sender->send('bar'));

        static::assertSame('bar', $two->await());

        static::assertTrue($one->isComplete());

        static::assertSame('foo', $one->await());
    }
}
